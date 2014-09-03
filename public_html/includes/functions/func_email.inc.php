<?php

  function email_send($parameters) {
    
    if (isset($parameters['sender']) && !is_array($parameters['sender'])) $parameters['sender'] = array('email' => settings::get('store_email'));
    if (empty($parameters['sender']['email'])) $parameters['sender']['email'] = settings::get('store_email');
    if (empty($parameters['sender']['name']) && $parameters['sender']['email'] == settings::get('store_email')) $parameters['sender']['name'] = settings::get('store_name');
    
    $parameters['sender']['name'] = str_replace(array("\r", "\n"), " ", trim($parameters['sender']['name']));
    $parameters['sender']['email'] = filter_var($parameters['sender']['email'], FILTER_SANITIZE_EMAIL);
    $parameters['subject'] = !empty($parameters['subject']) ? str_replace(array("\r", "\n"), " ", $parameters['subject']) : '(No subject)';
    $parameters['message'] = !empty($parameters['message']) ? $parameters['message'] : '';
    
    $parameters['formatted_sender'] = !empty($parameters['sender']['name']) ? '"'. $parameters['sender']['name'] .'" <'. $parameters['sender']['email'] .'>' : $parameters['sender']['email'];
    
  // Generate a boundary string
    $mime_boundary = '==Multipart_Boundary_x'. md5(time()) .'x';
    
    $headers = 'From: '. $parameters['formatted_sender'] . "\r\n"
             . 'Reply-To: '. $parameters['formatted_sender'] . "\r\n"
             . 'Return-Path: '. $parameters['formatted_sender'] . "\r\n"
             . 'MIME-Version: 1.0' . "\r\n"
             . 'Content-Type: multipart/mixed; boundary="'. $mime_boundary .'"' . "\r\n"
             . 'X-Mailer: LiteCart PHP/' . phpversion() . "\r\n\r\n";
    
  // Add a multipart boundary above the plain message
    $message = 'This is a multi-part message in MIME format.' . "\r\n\r\n"
              . '--' . $mime_boundary . "\r\n"
              . 'Content-Type: '. (!empty($parameters['html']) ? 'text/html' : 'text/plain') .'; charset='. language::$selected['charset'] . "\r\n"
              . 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n"
              //. 'This message was sent from computer '. gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' at '. date('Y-m-d H:i') .'.'. "\r\n\r\n"
              . $parameters['message'] . "\r\n\r\n";
              
  // Are there are attachments?
    if (!empty($parameters['attachments'])) {
      
      foreach ($parameters['attachments'] as $file) {
        $file = fopen($file, 'rb');
        $data = fread($file, filesize($file));
        fclose($file);
       
      // Add file attachment to the message
        $message .= '--'. $mime_boundary . "\r\n"
                 . 'Content-Type: '. @mime_content_type($file) .'; name="'. basename($file) .'"' . "\r\n"
                 . 'Content-Disposition: attachment; filename="'. basename($file) . '"' . "\r\n"
                 . 'Content-Transfer-Encoding: base64' . "\r\n\r\n"
                 . chunk_split(base64_encode($data)) . "\r\n\r\n"
                 . '--'. $mime_boundary .'--' . "\r\n";
      }
    }
    
    if (!is_array($parameters['recipients'])) $parameters['recipients'] = array($recipients);

    $success = true;
    foreach ($parameters['recipients'] as $recipient) {
      $recipient = filter_var($recipient, FILTER_SANITIZE_EMAIL);
      
      if (!mail($recipient, $parameters['subject'], $parameters['message'], $headers)) {
        trigger_error("Failed sending e-mail to ". $recipient, E_USER_WARNING);
        $success = false;
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