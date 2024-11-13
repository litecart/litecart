<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';

  document::$layout = 'default';

  if (!empty($_GET['code'])) {
    http_response_code((int)$_GET['code']);
  }

	if (preg_match('#\.(a?png|avif|gif|jpe?g|webp)$#', route::$request)) {
    echo file_get_contents('images/no_image.png');
    exit;
  }

  switch (http_response_code()) {

    case 400:
      $code = 400;
      $title = 'Bad Request';
      $description = language::translate('error_400_bad_request', 'The server cannot or will not process the request due to a client error.');
      break;

    case 401:
      $code = 401;
      $title = 'Unauthorized';
      $description = language::translate('error_401_unauthorized', 'You are not authorized to view the requested page.');
      break;

    case 403:
      $code = 403;
      $title = 'Forbidden';
      $description = language::translate('error_403_forbidden', 'Access to the requested page is forbidden.');
      break;

    case 404:
      $code = 404;
      $title = 'Not Found';
      $description = language::translate('error_404_not_found', 'The requested page could not be found.');
      break;

    case 410:
      $code = 410;
      $title = 'Gone';
      $description = language::translate('error_410_gone', 'The requested page is no longer available.');
      break;

    default:
      http_response_code($code = 500);
      $title = 'Internal Server Error';
      $description = language::translate('error_500_internal_server_error', 'Oops! That was not meant to happen. We don\'t know how to handle your request.');
      break;
  }

  $_page = new ent_view();
  $_page->snippets = [
    'code' => $code,
    'title' => $title,
    'description' => $description,
  ];
  echo $_page->stitch('pages/error_document');
