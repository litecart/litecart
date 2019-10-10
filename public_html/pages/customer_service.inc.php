<?php
  document::$snippets['title'][] = language::translate('customer_service:head_title', 'Customer Service');
  document::$snippets['description'] = language::translate('customer_service:meta_description', '');

  if (!empty($_GET['page_id'])) {
    breadcrumbs::add(language::translate('title_customer_service', 'Customer Service'), document::ilink('customer_service'));
  } else {
    breadcrumbs::add(language::translate('title_customer_service', 'Customer Service'));
  }

  $_page = new ent_view();

  ob_start();
  include vmod::check(FS_DIR_APP . 'includes/boxes/box_customer_service_links.inc.php');
  $_page->snippets['box_customer_service_links'] = ob_get_clean();

// Custom page
  if (!empty($_GET['page_id'])) {

    $page = reference::page($_GET['page_id']);

    if (empty($page->id)) {
      notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
      http_response_code(410);
      return;
    }

    if (empty($page->status)) {
      notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
      http_response_code(404);
      return;
    }

    document::$snippets['title'][] = !empty($page->head_title) ? $page->head_title : $page->title;
    document::$snippets['description'] = !empty($page->meta_description) ? $page->meta_description : '';

    breadcrumbs::add($page->title);

    $_page->snippets += array(
      'title' => $page->title,
      'content' => $page->content,
    );

  } else {

    ob_start();
    include vmod::check(FS_DIR_APP . 'includes/boxes/box_contact_us.inc.php');
    $_page->snippets['content'] = ob_get_clean();
  }

  echo $_page->stitch('pages/customer_service');
