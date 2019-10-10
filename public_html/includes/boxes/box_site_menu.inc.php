<?php

  $box_site_menu = new ent_view();

  $box_site_menu_cache_token = cache::token('box_site_menu', array('language'), 'file');
  if (!$box_site_menu->snippets = cache::get($box_site_menu_cache_token)) {

    $box_site_menu->snippets = array(
      'categories' => array(),
      'manufacturers' => array(),
      'pages' => array(),
    );

  // Categories

    $categories_query = functions::catalog_categories_query(0);

    while ($category = database::fetch($categories_query)) {
      $box_site_menu->snippets['categories'][$category['id']] = array(
        'type' => 'category',
        'id' => $category['id'],
        'title' => $category['name'],
        'link' => document::ilink('category', array('category_id' => $category['id'])),
        'image' => functions::image_thumbnail(FS_DIR_APP . 'images/' . $category['image'], 24, 24, 'CROP'),
        'priority' => $category['priority'],
      );
    }

  // Manufacturers

    $pages_query = database::query(
      "select id, name from ". DB_TABLE_MANUFACTURERS ."
      where status
      and featured
      order by name;"
    );

    while ($manufacturer = database::fetch($pages_query)) {
      $box_site_menu->snippets['manufacturers'][$manufacturer['id']] = array(
        'type' => 'manufacturer',
        'id' => $manufacturer['id'],
        'title' => $manufacturer['name'],
        'link' => document::ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])),
        'image' => null,
        'priority' => 0,
      );
    }

  // Information pages

    $pages_query = database::query(
      "select p.id, p.priority, pi.title from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where status
      and find_in_set('menu', dock)
      order by p.priority, pi.title;"
    );

    while ($page = database::fetch($pages_query)) {
      $box_site_menu->snippets['pages'][$page['id']] = array(
        'type' => 'page',
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::ilink('information', array('page_id' => $page['id'])),
        'image' => null,
        'priority' => $page['priority'],
      );
    }

    cache::set($box_site_menu_cache_token, $box_site_menu->snippets);
  }

  echo $box_site_menu->stitch('views/box_site_menu');
