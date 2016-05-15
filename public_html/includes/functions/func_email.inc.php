<?php

  function email_send($from_formatted, $recipients, $subject, $message, $html=false, $attachments=array()) {

    if (empty($from_formatted)) $from_formatted = settings::get('store_name') . ' <'. settings::get('store_email') .'>';

  // Secure
    $from_formatted = trim(str_replace(array("\r", "\n"), '', $from_formatted));
    $subject = trim(str_replace(array("\r", "\n"), '', $subject));

  // Extract
    $from_name = trim(trim(preg_replace('#^(.*)\s?<[^>]+>$#', '$1', $from_formatted)), '"');
    $from_email = filter_var(preg_replace('#^.*\s<([^>]+)>$#', '$1', $from_formatted), FILTER_SANITIZE_EMAIL);

  // Generate a boundary string
    $multipart_boundary_string = '==Multipart_Boundary_x'. md5(time()) .'x';

    if (strtoupper(language::$selected['charset']) == 'UTF-8') {
      $headers = 'From: '. (!empty($from_name) ? '=?utf-8?b?'. base64_encode($from_name) .'?= <'. $from_email .'>' : $from_email) . "\r\n"
               . 'Reply-To: '. $from_email . "\r\n"
               . 'Return-Path: '. $from_email . "\r\n"
               . 'MIME-Version: 1.0' . "\r\n"
               . 'Content-Type: multipart/mixed; boundary="'. $multipart_boundary_string . '"' . "\r\n"
               . 'X-Mailer: LiteCart PHP/' . phpversion() . "\r\n\r\n";
    } else {
      $headers = 'From: '. (!empty($from_name) ? $from_formatted : $from_email) . "\r\n"
               . 'Reply-To: '. $from_email . "\r\n"
               . 'Return-Path: '. $from_email . "\r\n"
               . 'MIME-Version: 1.0' . "\r\n"
               . 'Content-Type: multipart/mixed; boundary="'. $multipart_boundary_string . '"' . "\r\n"
               . 'X-Mailer: LiteCart PHP/' . phpversion() . "\r\n\r\n";
    }

  // Add a multipart boundary above the plain message
    $message = 'This is a multi-part message in MIME format.' . "\r\n\r\n"
              . '--' . $multipart_boundary_string . "\r\n"
              . 'Content-Type: '. (($html) ? 'text/html' : 'text/plain') .'; charset='. language::$selected['charset'] . "\r\n"
              . 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n"
              . $message . "\r\n\r\n";

  // Add file attachments to the message
    if (!empty($attachments)) {

      foreach ($attachments as $file) {
        $data = file_get_contents($file);

        $message .= '--'. $multipart_boundary_string . "\r\n"
                 . 'Content-Type: application/octet-stream; name="'. basename($file) .'"' . "\r\n"
                 . 'Content-Disposition: attachment; filename="'. basename($file) . '"' . "\r\n"
                 . 'Content-Transfer-Encoding: base64' . "\r\n\r\n"
                 . chunk_split(base64_encode($data)) . "\r\n\r\n";
      }
    }

    if (!is_array($recipients)) $recipients = preg_split('#>\s?(,|;|\r\n)#', $recipients);

    $success = true;
    foreach ($recipients as $to_formatted) {
      $to_name = trim(preg_replace('#^(.*)\s?<[^>]+>$#', '$1', $to_formatted), '" \r\n');
      $to_email = filter_var(preg_replace('#^.*\s<([^>]+)>$#', '$1', $to_formatted), FILTER_SANITIZE_EMAIL);

      if (strtoupper(language::$selected['charset']) == 'UTF-8') {
        if (!mail($to_email, '=?utf-8?b?'. base64_encode($subject) .'?=', $message, $headers)) {
          trigger_error("Failed sending email to ". $to_email, E_USER_WARNING);
          $success = false;
        }
      } else {
        if (!mail($to_email, $subject, $message, $headers)) {
          trigger_error("Failed sending email to ". $to_email, E_USER_WARNING);
          $success = false;
        }
      }
    }

    return $success;
  }

  function email_validate_address($address) {

    if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $address)) {
      return false;
    }

    return true;
  }

?>