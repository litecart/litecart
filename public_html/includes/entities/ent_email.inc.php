<?php

  class ent_email {
    public $data;
    public $previous;

    public function __construct($email_id=null, $charset=null) {

      if ($email_id !== null) {
        $this->load($email_id);
      } else {
        $this->reset();
      }

      $this->data['charset'] = $charset ? $charset : language::$selected['charset'];
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."emails;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field);
      }

      $this->data['sender'] = [
        'email' => settings::get('store_email'),
        'name' => settings::get('store_name'),
      ];

      $this->data['recipients'] = [];
      $this->data['ccs'] = [];
      $this->data['bccs'] = [];
      $this->data['multiparts'] = [];

      $this->previous = $this->data;

      return $this;
    }

    public function load($email_id) {

      if (!preg_match('#^[0-9]+$#', $email_id)) throw new Exception('Invalid email (ID: '. $email_id .')');

      $this->reset();

      $email_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."emails
        where id = ". (int)$email_id ."
        limit 1;"
      );

      if ($email = database::fetch($email_query)) {
        $this->data = array_replace($this->data, array_intersect_key($email, $this->data));
      } else {
        throw new Exception('Could not find email (ID: '. (int)$email_id .') in database.');
      }

      $this->data['sender'] = $email['sender'] ? json_decode($email['sender'], true) : [];
      $this->data['recipients'] = $email['recipients'] ? json_decode($email['recipients'], true) : [];
      $this->data['ccs'] = $email['ccs'] ? json_decode($email['ccs'], true) : [];
      $this->data['bccs'] = $email['bccs'] ? json_decode($email['bccs'], true) : [];
      $this->data['multiparts'] = $email['multiparts'] ? json_decode($email['multiparts'], true) : [];

      $this->previous = $this->data;

      return $this;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."emails
          (status, code, date_created) values
          ('". database::input($this->data['status']) ."', '". database::input($this->data['code']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."emails set
        status = '". (!empty($this->data['status']) ? database::input($this->data['status']) : 'draft') ."',
        code = '". database::input($this->data['code']) ."',
        charset = '". database::input($this->data['charset']) ."',
        sender = '". database::input(json_encode($this->data['sender'], JSON_UNESCAPED_SLASHES)) ."',
        recipients = '". database::input(json_encode($this->data['recipients'], JSON_UNESCAPED_SLASHES)) ."',
        ccs = '". database::input(json_encode($this->data['ccs'], JSON_UNESCAPED_SLASHES)) ."',
        bccs = '". database::input(json_encode($this->data['bccs'], JSON_UNESCAPED_SLASHES)) ."',
        subject = '". database::input($this->data['subject']) ."',
        multiparts = '". database::input(json_encode($this->data['multiparts'], JSON_UNESCAPED_SLASHES), true) ."',
        error = ". (!empty($this->data['error']) ? "'". database::input($this->data['error']) ."'" : "null") .",
        date_scheduled = ". (!empty($this->data['date_scheduled']) ? "'". database::input($this->data['date_scheduled']) ."'" : "null") .",
        date_sent = ". (!empty($this->data['date_sent']) ? "'". database::input($this->data['date_sent']) ."'" : "null") .",
        date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] .";"
      );

      $this->previous = $this->data;

      $this->cleanup();

      cache::clear_cache('email');
    }

    public function set_sender($email, $name=null) {

      if (empty($name)) $name = preg_replace('#"?(.*)"?\s*<[^>]+>#', '$1', $email);

      $email = trim(preg_replace('#^.*\s<([^>]+)>$#', '$1', $email));
      $name = trim(preg_replace('#(\R|\t|%0A|%0D)*#', '', $name));

      if (!functions::validate_email($email)) throw new Exception('Invalid email address ('. $email .')');

      $this->data['sender'] = [
        'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
        'name' => $name,
      ];

      return $this;
    }

    public function set_subject($subject) {

      $this->data['subject'] = trim(preg_replace('#(\R|\t|%0A|%0D)*#', '', $subject));

      return $this;
    }

    public function add_body($content, $html=false, $charset=null) {

      if (empty($content)) {
        trigger_error('Cannot add an email body with empty content', E_USER_WARNING);
        return $this;
      }

      if (!$charset) {
        $charset = $this->data['charset'];
      }

    // Convert all line endings to RFC standard \r\n
      $content = preg_replace('#\r\n?|\n#', "\r\n", $content);

      $this->data['multiparts'][] = [
        'headers' => [
          'Content-Type' => ($html ? 'text/html' : 'text/plain') .'; charset='. $charset,
          'Content-Transfer-Encoding' => '8bit',
        ],
        'body' => trim($content),
      ];

      return $this;
    }

    public function add_attachment($file, $filename=null, $parse_as_string=false) {

      if (!$filename) $filename = pathinfo($file, PATHINFO_BASENAME);

      $data = $parse_as_string ? $file : file_get_contents($file);

      if ($parse_as_string) {
        file_put_contents($tmpfile=tempnam(sys_get_temp_dir(), 'lc_'), $data);
        $mime_type = mime_content_type($tmpfile);
        unlink($tmpfile);
      } else {
        $mime_type = mime_content_type($file);
      }

      $this->data['multiparts'][] = [
        'headers' => [
          'Content-Type' => $mime_type,
          'Content-Disposition' => 'attachment; filename="'. basename($filename) . '"',
          'Content-Transfer-Encoding' => 'base64',
        ],
        'body' => chunk_split(base64_encode($data)),
      ];

      return $this;
    }

    public function add_recipient($email, $name=null) {

      if (empty($name)) $name = preg_replace('#"?(.*)"?\s*<[^>]+>#', '$1', $email);

      $email = trim(preg_replace('#^.*\s<([^>]+)>$#', '$1', $email));
      $name = trim(preg_replace('#(\R|\t|%0A|%0D)*#', '', $name));

      if (!functions::validate_email($email)) throw new Exception('Invalid email address ('. $email .')');

      $this->data['recipients'][] = [
        'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
        'name' => $name,
      ];

      return $this;
    }

    public function add_cc($email, $name=null) {

      if (empty($name)) $name = preg_replace('#"?(.*)"?\s*<[^>]+>#', '$1', $email);

      $email = trim(preg_replace('#^.*\s<([^>]+)>$#', '$1', $email));
      $name = trim(preg_replace('#(\R|\t|%0A|%0D)*#', '', $name));

      if (!functions::validate_email($email)) {
        throw new Error('Invalid email address ('. $email .')');
      }

      $this->data['ccs'][] = [
        'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
        'name' => $name,
      ];

      return $this;
    }

    public function add_bcc($email, $name=null) {

      if (empty($name)) $name = preg_replace('#"?(.*)"?\s*<[^>]+>#', '$1', $email);

      $email = trim(preg_replace('#^.*\s<([^>]+)>$#', '$1', $email));
      $name = trim(preg_replace('#(\R|\t|%0A|%0D)*#', '', $name));

      if (!functions::validate_email($email)) {
        throw new Error('Invalid email address ('. $email .')');
      }

      $this->data['bccs'][] = [
        'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
        'name' => $name,
      ];

      return $this;
    }

    public function format_contact($contact) {

      if (empty($contact['name']) || $contact['name'] == $contact['email']) {
        return $contact['email'];
      }

      if (strtoupper(language::$selected['charset']) == 'UTF-8') {
        return mb_encode_mimeheader($contact['name']) .' <'. $contact['email'] .'>';
      }

      if (preg_match('#[,;"]#', $contact['name'])) {
        return '"'. addcslashes($contact['name'], '"') .'" <'. $contact['email'] .'>';
      }

      return $contact['name'] .' <'. $contact['email'] .'>';
    }

    public function cleanup($time_ago='-30 days') {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."emails
        where status in ('sent', 'error')
        and date_updated < '". date('Y-m-d H:i:s', strtotime($time_ago)) ."';"
      );

      cache::clear_cache('email');
    }

    public function send() {

      if (!settings::get('email_status')) return;

      try {

        $this->save();

        if ($this->data['status'] == 'sent') {
          trigger_error('Email already marked as sent', E_USER_WARNING);
          return false;
        }

        if (!$this->data['multiparts']) {
          throw new Exception('Cannot send email with an empty body');
        }

      // Prepare headers
        $headers = [
          'Date' => date('r'),
          'From' => $this->format_contact(['name' => settings::get('store_name'), 'email' => settings::get('store_email')]),
          'Sender' => $this->format_contact($this->data['sender']),
          'Reply-To' => $this->format_contact($this->data['sender']),
          'Return-Path' => settings::get('store_email'),
          'MIME-Version' => '1.0',
          'X-Mailer' => PLATFORM_NAME .'/'. PLATFORM_VERSION,
          'X-Sender' => $this->format_contact($this->data['sender']),
        ];

      // Add "To" header
        if (!empty($this->data['recipients'])) {
          $tos = [];
          foreach ($this->data['recipients'] as $to) {
            $tos[] = $this->format_contact($to);
          }
          $headers['To'] = implode(', ', $tos);
        }

      // Add "Cc" header
        if (!empty($this->data['ccs'])) {
          $ccs = [];
          foreach ($this->data['ccs'] as $cc) {
            $ccs[] = $this->format_contact($cc);
          }
          $headers['Cc'] = implode(', ', $ccs);
        }

      // SMTP does not need a header for BCCs, we will add that for PHP mail() later

      // Prepare subject
        $headers['Subject'] = mb_encode_mimeheader($this->data['subject']);

        if (count($this->data['multiparts']) > 1) {
          $multipart_boundary_string = '==Multipart_Boundary_x'. md5(time()) .'x';
          $headers['Content-Type'] = 'multipart/mixed; boundary="'. $multipart_boundary_string . '"' . "\r\n";
        }

        $body = '';

      // Prepare several multiparts
        if (count($this->data['multiparts']) > 1) {
          foreach ($this->data['multiparts'] as $multipart) {
            $body .= '--'. $multipart_boundary_string . "\r\n"
                  . implode("\r\n", array_map(function($v, $k) { return $k.':'.$v; }, $multipart['headers'], array_keys($multipart['headers']))) . "\r\n\r\n"
                  . $multipart['body'] . "\r\n\r\n";
          }

          $body .= '--'. $multipart_boundary_string .'--';

      // Prepare one multipart only
        } else {
          $headers = array_merge($headers, $this->data['multiparts'][0]['headers']);
          $body .= $this->data['multiparts'][0]['body'];
        }

      // Deliver via SMTP
        if (settings::get('smtp_status')) {

          $smtp = new wrap_smtp(
            settings::get('smtp_host'),
            settings::get('smtp_port'),
            settings::get('smtp_username'),
            settings::get('smtp_password')
          );

          $smtp->connect();

          $recipients = [];

          foreach ($this->data['recipients'] as $recipient) {
            $recipients[] = $recipient['email'];
          }

          foreach ($this->data['ccs'] as $cc) {
            $recipients[] = $cc['email'];
          }

          foreach ($this->data['bccs'] as $bcc) {
            $recipients[] = $bcc['email'];
          }

          array_walk($headers, function (&$v, $k) { $v = "$k: $v"; });

          $data = implode("\r\n", $headers) . "\r\n\r\n"
                . $body;

          $result = $smtp->send(settings::get('store_email'), $recipients, $data);

          $smtp->disconnect();

      // Deliver via PHP mail()
        } else {

          unset($headers['To']);
          unset($headers['Subject']);

        // PHP mail() needs a header for BCCs
          if (!empty($this->data['bccs'])) {
            $bccs = [];
            foreach ($this->data['bccs'] as $bcc) {
              $bccs[] = $this->format_contact($bcc);
            }
            $headers['Bcc'] = implode(', ', $bccs);
          }

          $recipients = [];
          foreach ($this->data['recipients'] as $recipient) {
            $recipients[] = $this->format_contact($recipient);
          }
          $recipients = implode(', ', $recipients);

          $subject = mb_encode_mimeheader($this->data['subject']);

          array_walk($headers, function (&$v, $k) { $v = "$k: $v"; });
          $headers = implode("\r\n", $headers);

          if (!$result = mail($recipients, $subject, $body, $headers)) {
            trigger_error('Failed sending email "'. $this->data['subject'] .'"', E_USER_WARNING);
          }
        }

        if (!empty($result)) {
          $this->data['status'] = 'sent';
          $this->data['date_sent'] = date('Y-m-d H:i:s');
        } else {
          $this->data['status'] = 'error';
        }

        $this->save();

      } catch (Exception $e) {

        trigger_error('Failed sending email "'. $this->data['subject'] .'": '. $e->getMessage(), E_USER_WARNING);

        $this->data['status'] = 'error';
        $this->data['error'] = $e->getMessage();
        $this->save();
      }

      return !empty($result) ? true : false;
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."emails
        where id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('email');
    }
  }
