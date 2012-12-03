<?php

  function email_send($from, $recipients, $subject='(No subject)', $message, $html=false, $attachments=array()) {
    global $system;
    
    if (empty($from)) $from = $system->settings->get('store_name') . ' <'. $system->settings->get('store_email') .'>';
    
    if (!is_array($recipients)) $recipients = array($recipients);
    
    $from = str_replace(array("\r", "\n", ":"), "", $from);
    
  // Generate a boundary string    
    $mime_boundary = '==Multipart_Boundary_x'. md5(time()) .'x';

    $headers = 'From: '. $from . "\r\n"
             . 'Reply-To: '. $from . "\r\n"
             . 'Return-Path: '. $from . PHP_EOL
             . 'MIME-Version: 1.0' . "\r\n"
             . 'Content-Type: multipart/mixed; boundary="'. $mime_boundary . '"' . "\r\n"
             . 'X-Mailer: PHP/' . phpversion() . "\r\n\r\n";
     
  // Add a multipart boundary above the plain message
    $message = 'This is a multi-part message in MIME format.' . "\r\n\r\n"
              . '--' . $mime_boundary . "\r\n"
              . 'Content-Type: '. (($html) ? 'text/html' : 'text/plain') .'; charset='. $system->language->selected['charset'] . "\r\n"
              . 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n"
              //. 'This message was sent from computer '. gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' at '. date('Y-m-d H:i') .'.'. "\r\n\r\n"
              . $message . "\r\n\r\n";
              
  // Are there are attachments?
    if (!empty($attachments)) {
      
      foreach ($attachments as $file) {
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
    
    $success = true;
    foreach ($recipients as $to) {
      
      $to = str_replace(array("\r", "\n", ":"), "", $to);
      
      if (!mail($to, $subject, $message, $headers)) {
        trigger_error("Failed sending e-mail to ". $to, E_USER_WARNING);
        $success = false;
      }
    }
    
    return $success;
  }

  function email_validate_address($address) {
    
    if( !preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $address)) {
      return false;
    }
    
    return true;
  }

?>