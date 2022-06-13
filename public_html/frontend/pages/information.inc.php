<?php

  try {

    if (empty($_GET['page_id'])) throw new Exception('Missing page_id', 400);

    $page = reference::page($_GET['page_id']);

    if (empty($page->id)) {
      http_response_code(410);
      include 'app://frontend/pages/error_document.inc.php';
      return;
    }

    if (empty($page->status)) {
      http_response_code(404);
      include 'app://frontend/pages/error_document.inc.php';
      return;
    }

    $mother_page = array_values($page->path)[0];
    if (!in_array('information', $page->dock) && !in_array('information', $mother_page->dock)
     && !in_array('menu', $page->dock) && !in_array('menu', $mother_page->dock)) {
      http_response_code(404);
      include 'app://frontend/pages/error_document.inc.php';
      return;
    }

    document::$snippets['title'][] = !empty($page->head_title) ? $page->head_title : $page->title;
    document::$snippets['description'] = !empty($page->meta_description) ? $page->meta_description : '';

    breadcrumbs::add(language::translate('title_information', 'Information'));
    foreach (array_slice($page->path, 0, -1, true) as $crumb) {
      breadcrumbs::add($crumb->title, document::ilink('information', ['page_id' => $crumb->id]));
    }
    breadcrumbs::add($page->title);

    $_page = new ent_view(FS_DIR_TEMPLATE . 'pages/information.inc.php');

    $_page->snippets = [
      'title' => $page->title,
      'content' => $page->content,
    ];

    echo $_page;

  } catch (Exception $e) {
    http_response_code($e->getCode());
    //notices::add('errors', $e->getMessage());
    include 'app://frontend/pages/error_document.inc.php';
    return;
  }
