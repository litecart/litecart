<?php
  if (!isset($_GET['code'])) $_GET['code'] = 404;

  switch ((int)$_GET['code']) {
    case 403:
      notices::add('errors', language::translate('error_403_forbidden', 'Access to the requested file is forbidden'));
      break;
    case 404:
      notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
      break;
    case 410:
      notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
      break;
    default:
      notices::add('errors', language::translate('error_400_bad_request', 'The server cannot or will not process the request due to a client error.'));
      break;
  }

  if (preg_match('#\.(jpg|png|gif)$#', route::$request)) {
    echo file_get_contents(WS_DIR_IMAGES . 'no_image.png');
    exit;
  }

  http_response_code($_GET['code']);
  header('Refresh: 0; url='. document::ilink(''));
  exit;
