<?php

  class email {
    private $_charset = 'UTF-8';
    private $_sender = array();
    private $_recipients = array();
    private $_subject = '';
    private $_multiparts = array();

    public function __construct($charset=null) {

      $this->_charset = $charset ? $charset : language::$selected['charset'];

      $this->set_sender(settings::get('store_email'), settings::get('store_name'));

      return $this;
    }

    public function set_sender($email, $name=null) {

      $email = trim($email);

      if (!$this->validate_email_address($email)) trigger_error('Invalid email address ('. $email .')', E_USER_ERROR);

      $this->_sender = array(
        'email' => filter_var(preg_replace('#^.*\s<([^>]+)>$#', '$1', $email), FILTER_SANITIZE_EMAIL),
        'name' => $name ? $name : trim(trim(preg_replace('#^(.*)\s?<[^>]+>$#', '$1', $email)), '"'),
      );

      return $this;
    }

    public function set_subject($subject) {

      $this->_subject = trim(preg_replace('#\R#', '', $subject));

      return $this;
    }

    public function add_body($content, $html=false, $charset=null) {

      if (empty($content)) {
        trigger_error('Cannot add an email body with empty content', E_USER_WARNING);
        return $this;
      }

      if (!$charset) $charset = $this->_charset;

      $this->_multiparts[] = 'Content-Type: '. ($html ? 'text/html' : 'text/plain') .'; charset='. $charset . "\r\n"
                           . 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n"
                           . trim($content);

      return $this;
    }

    public function add_attachment($file, $filename=null, $parse_as_string=false) {

      if (!$filename) $filename = pathinfo($file, PATHINFO_BASENAME);

      $data = $parse_as_string ? $file : file_get_contents($file);

      $this->_multiparts[] = 'Content-Type: application/octet-stream' . "\r\n"
                           . 'Content-Disposition: attachment; filename="'. basename($filename) . '"' . "\r\n"
                           . 'Content-Transfer-Encoding: base64' . "\r\n\r\n"
                           . chunk_split(base64_encode($data)) . "\r\n\r\n";

      return $this;
    }

    public function add_recipient($email, $name=null) {

      $email = trim($email);

      if (!$this->validate_email_address($email)) trigger_error('Invalid email address ('. $email .')', E_USER_ERROR);

      $this->_recipients[] = array(
        'email' => filter_var(preg_replace('#^.*\s<([^>]+)>$#', '$1', $email), FILTER_SANITIZE_EMAIL),
        'name' => $name ? $name : trim(trim(preg_replace('#^(.*)\s?<[^>]+>$#', '$1', $email)), '"'),
      );

      return $this;
    }


    public function add_cc($email, $name=null) {

      $email = trim($email);

      if (!$this->validate_email_address($email)) trigger_error('Invalid email address ('. $email .')', E_USER_ERROR);

      $this->_cc[] = array(
        'email' => filter_var(preg_replace('#^.*\s<([^>]+)>$#', '$1', $email), FILTER_SANITIZE_EMAIL),
        'name' => $name ? $name : trim(trim(preg_replace('#^(.*)\s?<[^>]+>$#', '$1', $email)), '"'),
      );

      return $this;
    }

    public function add_bcc($email, $name=null) {

      $email = trim($email);

      if (!$this->validate_email_address($email)) trigger_error('Invalid email address ('. $email .')', E_USER_ERROR);

      $this->_bcc[] = array(
        'email' => filter_var(preg_replace('#^.*\s<([^>]+)>$#', '$1', $email), FILTER_SANITIZE_EMAIL),
        'name' => $name ? $name : trim(trim(preg_replace('#^(.*)\s?<[^>]+>$#', '$1', $email)), '"'),
      );

      return $this;
    }

    public function format_contact($contact) {

      if (empty($contact['name'])) return '<'. $contact['email'] .'>';

      if (strtoupper(language::$selected['charset']) == 'UTF-8') {
        return '=?utf-8?b?'. base64_encode($contact['name']) .'?= <'. $contact['email'] .'>';
      }

      if (strpos($contact['name'], '"') !== false || strpos($contact['name'], ',') !== false) {
        return '"'. addcslashes($contact['name'], '"') .'" <'. $contact['email'] .'>';
      }

      return $contact['name'] .' <'. $contact['email'] .'>';
    }

    public function validate_email_address($email) {

      return preg_match('#^([a-zA-Z0-9])+([a-zA-Z0-9\+\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$#', $email) ? true : false;
    }

    public function send() {

      if (!settings::get('email_status')) {
        notices::add('warning', language::translate('warning_email_disabled', 'Please note the email service is disabled so no mails have been sent.'), 'email_disabled');
        return true;
      }

    // Perpare headers
      $headers = array(
        'From' => $this->format_contact($this->_sender),
        'Reply-To' => $this->format_contact($this->_sender),
        'Return-Path' => $this->format_contact($this->_sender),
        'MIME-Version' => '1.0',
        'X-Mailer' => PLATFORM_NAME .'/'. PLATFORM_VERSION,
      );

    // Add CCs
      if (!empty($this->_cc)) {
        $ccs = array();
        foreach ($this->_cc as $cc) {
          $ccs[] = $this->format_contact($cc);
        }
        $headers['Cc'] = implode(', ', $ccs);
      }

    // Add BCCs
      if (!empty($this->_bcc)) {
        $bccs = array();
        foreach ($this->_bcc as $bcc) {
          $bccs[] = $this->format_contact($bcc);
        }
        $headers['Bcc'] = implode(', ', $bccs);
      }

      $multipart_boundary_string = '==Multipart_Boundary_x'. md5(time()) .'x';
      $headers['Content-Type'] = 'multipart/mixed; boundary="'. $multipart_boundary_string . '"' . "\r\n";

      array_walk($headers,
        function (&$v, $k) {
          $v = $k.': '.$v;
        }
      );

      $headers = implode("\r\n", $headers);

    // Prepare subject
      if (strtoupper(language::$selected['charset']) == 'UTF-8') {
        $subject = '=?utf-8?b?'. base64_encode($this->_subject) .'?=';
      } else {
        $subject = $this->_subject;
      }

    // Prepare body
      $body = '';
      foreach ($this->_multiparts as $multipart) {
        $body .= '--'. $multipart_boundary_string . "\r\n"
               . $multipart . "\r\n\r\n";
      }

      if (empty($body)) {
        trigger_error('Cannot send email with an empty body', E_USER_WARNING);
        return false;
      }

    // Deliver via SMTP
      if (settings::get('smtp_status')) {

        $smtp = new smtp(
          settings::get('smtp_host'),
          settings::get('smtp_port'),
          settings::get('smtp_username'),
          settings::get('smtp_password')
        );

        foreach ($this->_recipients as $recipient) {

          $data = 'To: ' . $this->format_contact($recipient) . "\r\n"
                . 'Subject: ' . $subject . "\r\n"
                . $headers . "\r\n"
                . $body;
          try {
            $result = $smtp->send($this->_sender['email'], $recipient['email'], $data);
          } catch(Exception $e) {
            trigger_error('Failed sending email to '. $recipient['email'] .': '. $e->getMessage(), E_USER_WARNING);
          }
        }

        $smtp->disconnect();

    // Deliver via PHP mail()
      } else {

        foreach ($this->_recipients as $recipient) {
          if (!$result = mail($this->format_contact($recipient), $subject, $body, $headers)) {
            trigger_error('Failed sending email to '. $recipient['email'], E_USER_WARNING);
          }
        }
      }

      return !empty($result) ? true : false;
    }
  }
