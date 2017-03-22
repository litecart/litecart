<?php
  document::$snippets['title'][] = language::translate('customer_service:head_title', 'Customer Service');
  document::$snippets['description'] = language::translate('customer_service:meta_description', '');

  if (!empty($_GET['page_id'])) {
    breadcrumbs::add(language::translate('title_customer_service', 'Customer Service'), document::ilink('customer_service'));
  } else {
    breadcrumbs::add(language::translate('title_customer_service', 'Customer Service'));
  }

  $_page = new view();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_customer_service_links.inc.php');
  $_page->snippets['box_customer_service_links'] = ob_get_clean();

// Custom page
  if (!empty($_GET['page_id'])) {

    $pages_query = database::query(
      "select p.id, p.status, pi.title, pi.content, pi.head_title, pi.meta_description from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where p.id = '". (int)$_GET['page_id'] ."'
      limit 1;"
    );
    $page = database::fetch($pages_query);

    if (empty($page['status'])) {
      notices::add('errors', language::translate('error_page_not_found', 'The requested page could not be found'));
      http_response_code(404);
      header('Location: '. document::ilink(''));
      exit;
    }

    document::$snippets['title'][] = !empty($page['head_title']) ? $page['head_title'] : $page['title'];
    document::$snippets['keywords'] = !empty($page['meta_keywords']) ? $page['meta_keywords'] : '';
    document::$snippets['description'] = !empty($page['meta_description']) ? $page['meta_description'] : '';

    breadcrumbs::add($page['title']);

    $_page->snippets += array(
      'title' => $page['title'],
      'content' => $page['content'],
    );

  } else {

    ob_start();
    include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_contact_us.inc.php');
    $_page->snippets['content'] = ob_get_clean();
  }

  echo $_page->stitch('pages/customer_service');
