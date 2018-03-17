<?php

  $page = reference::page($_GET['page_id']);

  if (empty($page->id)) {
    notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
    http_response_code(410);
    header('Refresh: 0; url='. document::ilink(''));
    die('HTTP Error 410 Gone');
  }

  if (empty($page->status)) {
    notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
    http_response_code(404);
    header('Refresh: 0; url='. document::ilink(''));
    die('HTTP Error 404 Not Found');
  }

  document::$snippets['title'][] = !empty($page->head_title) ? $page->head_title : $page->title;
  document::$snippets['description'] = !empty($page->meta_description) ? $page->meta_description : '';

  breadcrumbs::add($page->title);

  $_page = new view();

  $_page->snippets = array(
    'title' => $page->title,
    'content' => $page->content,
  );

  echo $_page->stitch('pages/information');
