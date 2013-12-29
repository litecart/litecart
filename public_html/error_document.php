<?php
  require_once('includes/app_header.inc.php');
  
  if (!isset($_GET['code'])) $_GET['code'] = 404;
  
  switch ($_GET['code']) {
    case 403:
      notices::add('errors', language::translate('error_403_forbidden', 'Access to the requested file is forbidden'));
      header('HTTP/1.1 403 Forbidden');
      header('Location: '. document::link(WS_DIR_HTTP_HOME));
      break;
    case 404:
      notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
      header('HTTP/1.1 404 Not Found');
      header('Location: '. document::link(WS_DIR_HTTP_HOME));
      break;
    case 410:
      notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
      header('HTTP/1.1 410 Gone');
      header('Location: '. document::link(WS_DIR_HTTP_HOME));
      break;
    default:
      header('HTTP/1.1 400 Bad Request');
      header('Location: '. document::link(WS_DIR_HTTP_HOME));
  }
  
?>