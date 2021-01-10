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
  include vmod::check(FS_DIR_APP . 'frontend/boxes/box_customer_service_links.inc.php');
  $_page->snippets['box_customer_service_links'] = ob_get_clean();

// Custom page
  if (!empty($_GET['page_id'])) {

    $page = reference::page($_GET['page_id']);

    if (empty($page->id)) {
      notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
      include vmod::check(FS_DIR_APP . 'frontend/pages/error_document.inc.php');
      return;
    }

    if (empty($page->status)) {
      notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
      include vmod::check(FS_DIR_APP . 'frontend/pages/error_document.inc.php');
      return;
    }

    document::$snippets['title'][] = !empty($page->head_title) ? $page->head_title : $page->title;
    document::$snippets['description'] = !empty($page->meta_description) ? $page->meta_description : '';

    foreach (array_slice($page->path, 0, -1, true) as $crumb) {
      breadcrumbs::add($crumb->title, document::ilink('customer_service', ['page_id' => $crumb->id]));
    }
    breadcrumbs::add($page->title);

    $_page->snippets += [
      'title' => $page->title,
      'content' => $page->content,
    ];

  } else {

    ob_start();
    include vmod::check(FS_DIR_APP . 'frontend/boxes/box_contact_us.inc.php');
    $_page->snippets['content'] = ob_get_clean();
  }

  echo $_page->stitch('pages/customer_service.inc.php');
