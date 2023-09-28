<?php
  $box_site_footer_cache_token = cache::token('box_site_footer', ['language', 'login', 'region'], 'file');
  if (cache::capture($box_site_footer_cache_token)) {

    $box_site_footer = new ent_view();

    $box_site_footer->snippets = [
      'categories' => [],
      'manufacturers' => [],
      'pages' => [],
    ];

  // Categories
    $categories_query = database::query(
      "select c.id, ci.name
      from ". DB_TABLE_PREFIX ."categories c
      left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
      where c.status
      and c.parent_id = 0
      order by c.priority asc, ci.name asc;"
    );

    $i = 0;
    while ($category = database::fetch($categories_query)) {
      if (++$i < 10) {
        $box_site_footer->snippets['categories'][$category['id']] = [
          'id' => $category['id'],
          'name' => $category['name'],
          'link' => document::ilink('category', ['category_id' => $category['id']]),
        ];
      } else {
        $box_site_footer->snippets['categories'][] = [
          'id' => 0,
          'name' => language::translate('title_more', 'More') . '…',
          'link' => document::ilink('categories'),
        ];
        break;
      }
    }

  // Manufacturers
    $manufacturers_query = database::query(
      "select m.id, m.name
      from ". DB_TABLE_PREFIX ."manufacturers m
      where status
      and featured
      order by m.name asc;"
    );

    $i = 0;
    while ($manufacturer = database::fetch($manufacturers_query)) {
      if (++$i < 10) {
        $box_site_footer->snippets['manufacturers'][$manufacturer['id']] = [
          'id' => $manufacturer['id'],
          'name' => $manufacturer['name'],
          'link' => document::ilink('manufacturer', ['manufacturer_id' => $manufacturer['id']]),
        ];
      } else {
        $box_site_footer->snippets['manufacturers'][] = [
          'id' => 0,
          'name' => language::translate('title_more', 'More') . '…',
          'link' => document::ilink('manufacturers'),
        ];
        break;
      }
    }

  // Information Pages
    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PREFIX ."pages p
      left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where status
      and dock = 'information'
      order by p.priority, pi.title;"
    );

    while ($page = database::fetch($pages_query)) {
      $box_site_footer->snippets['pages'][$page['id']] = [
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::ilink('information', ['page_id' => $page['id']]),
      ];
    }

 // Customer Service Pages
    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PREFIX ."pages p
      left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where status
      and dock = 'customer_service'
      order by p.priority, pi.title;"
    );

    while ($page = database::fetch($pages_query)) {
      $box_site_footer->snippets['customer_service_pages'][$page['id']] = [
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::ilink('customer_service', ['page_id' => $page['id']]),
      ];
    }

    echo $box_site_footer->stitch('views/box_site_footer');

    cache::end_capture($box_site_footer_cache_token);
  }
