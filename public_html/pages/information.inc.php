<?php

  $page = reference::page($_GET['page_id']);

  if (empty($page->id)) {
    http_response_code(410);
    echo language::translate('error_410_gone', 'The requested file is no longer available');
    return;
  }

  if (empty($page->status)) {
    http_response_code(404);
    echo language::translate('error_404_not_found', 'The requested file could not be found');
    return;
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
