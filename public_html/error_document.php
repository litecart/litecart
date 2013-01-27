<?php
  require_once('includes/app_header.inc.php');
  
  if (!isset($_GET['code'])) $_GET['code'] = 404;
  
  switch ($_GET['code']) {
    case 404:
      $system->notices->add('errors', $system->language->translate('error_404_not_found', 'The requested page could not be found'));
      header('HTTP/1.1 404 Not Found');
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME));
      break;
    case 410:
      $system->notices->add('errors', $system->language->translate('error_410_gone', 'The requested page is no longer available'));
      header('HTTP/1.1 410 Gone');
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME));
      break;
    default:
      header('HTTP/1.1 400 Bad Request');
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME));
  }
  
?>