<?php

  document::$layout = 'default';

  if (!empty($_GET['code'])) {
    http_response_code((int)$_GET['code']);
  }

	if (preg_match('#\.(avif|gif|jpg|png|webp)$#', route::$request)) {
    echo file_get_contents('images/no_image.png');
    exit;
  }

  $_page = new ent_view();
  $_page->snippets['code'] = http_response_code();

  switch (http_response_code()) {

    case 400:
      $_page->snippets['title'] = 'Bad Request';
      $_page->snippets['description'] = language::translate('error_400_bad_request', 'The server cannot or will not process the request due to a client error.');
      break;

    case 401:
      $_page->snippets['title'] = 'Unauthorized';
      $_page->snippets['description'] = language::translate('error_401_unauthorized', 'You are not authorized to view the requested file.');
      break;

    case 403:
      $_page->snippets['title'] = 'Forbidden';
      $_page->snippets['description'] = language::translate('error_403_forbidden', 'Access to the requested file is forbidden.');
      break;

    case 404:
      $_page->snippets['title'] = 'Not Found';
      $_page->snippets['description'] = language::translate('error_404_not_found', 'The requested file could not be found.');
      break;

    case 410:
      $_page->snippets['title'] = 'Gone';
      $_page->snippets['description'] = language::translate('error_410_gone', 'The requested page is no longer available.');
      break;

    default:
      http_response_code(500);
      $_page->snippets['title'] = 'Internal Server Error';
      $_page->snippets['description'] = language::translate('error_500_internal_server_error', 'Oops! That was not meant to happen. We don\'t know how to handle your request.');
      break;
  }

  echo $_page->stitch('pages/error_document');
