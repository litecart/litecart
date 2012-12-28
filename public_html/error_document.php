<?php
  require_once('includes/app_header.inc.php');
  
  if (!isset($_GET['code'])) $_GET['code'] = 404;
  
  switch ($_GET['code']) {
    case 404:
      
      header('HTTP/1.0 404 Not Found');
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME));
      break;
    case 410:
      $system->notices->add('errors', $system->language->translate('error_410_gone', 'The requested page has been permanently removed'));
      header('HTTP/1.0 410 Gone');
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME));
      break;
    default:
      header('HTTP/1.0 400 Bad Request');
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME));
  }
  
?>