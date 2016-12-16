<?php

// Information page
  $pages_query = database::query(
    "select p.id, p.status, pi.title, pi.content, pi.head_title, pi.meta_description from ". DB_TABLE_PAGES ." p
    left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
    where p.id = '". (int)$_GET['page_id'] ."'
    limit 1;"
  );
  $page = database::fetch($pages_query);

  if (empty($page['status'])) {
    notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
    http_response_code(410);
    header('Refresh: 0; url='. document::ilink(''));
    exit;
  }

  if (empty($page['status'])) {
    notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
    http_response_code(404);
    header('Refresh: 0; url='. document::ilink(''));
    exit;
  }

  document::$snippets['title'][] = !empty($page['head_title']) ? $page['head_title'] : $page['title'];
  document::$snippets['description'] = !empty($page['meta_description']) ? $page['meta_description'] : '';

  breadcrumbs::add($page['title']);

  $_page = new view();

  $_page->snippets = array(
    'title' => $page['title'],
    'content' => $page['content'],
  );

  echo $_page->stitch('pages/information');
?>