<?php

  try {

    if (empty($_GET['page_id'])) throw new Exception('Missing page_id', 400);

    $page = reference::page($_GET['page_id']);

    if (empty($page->id)) {
      throw new Exception(language::translate('error_410_gone', 'The requested page is no longer available'), 410);
    }

    if (empty($page->status)) {
      throw new Exception(language::translate('error_404_not_found', 'The requested file could not be found'), 404);
    }

    document::$snippets['title'][] = !empty($page->head_title) ? $page->head_title : $page->title;
    document::$snippets['description'] = !empty($page->meta_description) ? $page->meta_description : '';

    breadcrumbs::add(language::translate('title_information', 'Information'));
    foreach (array_slice($page->path, 0, -1, true) as $crumb) {
      breadcrumbs::add($crumb->title, document::ilink('information', ['page_id' => $crumb->id]));
    }
    breadcrumbs::add($page->title);

    $_page = new ent_view('pages/information.inc.php');

    $_page->snippets = [
      'title' => $page->title,
      'content' => $page->content,
    ];

    echo $_page;

  } catch (Exception $e) {
    http_response_code($e->getCode());
    //notices::add('errors', $e->getMessage());
    include vmod::check(FS_DIR_APP . 'frontend/pages/error_document.inc.php');
    return;
  }
