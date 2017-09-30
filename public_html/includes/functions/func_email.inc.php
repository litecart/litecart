<?php

  function email_send($from_formatted, $recipients, $subject, $message, $html=false, $attachments=array()) {

    $from_email = filter_var(preg_replace('#^.*\s<([^>]+)>$#', '$1', $from_formatted), FILTER_SANITIZE_EMAIL);
    $from_name = trim(trim(preg_replace('#^(.*)\s?<[^>]+>$#', '$1', $from_formatted)), '"');

    $email = new email();

    $email->set_sender($from_email, $from_name)
          ->set_subject($subject)
          ->add_body($message, $html);

    foreach ($attachments as $attachment) {
      $email->add_attachment($attachment);
    }

    foreach ($recipients as $to_formatted) {
      $to_name = trim(preg_replace('#^(.*)\s?<[^>]+>$#', '$1', $to_formatted), '" \r\n');
      $to_email = filter_var(preg_replace('#^.*\s<([^>]+)>$#', '$1', $to_formatted), FILTER_SANITIZE_EMAIL);

      $email->add_recipient($to_email, $to_name);
    }

    return $email->send();
  }

  function email_validate_address($address) {

    if (preg_match('#^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$#', $address)) return true;

    return false;
  }
