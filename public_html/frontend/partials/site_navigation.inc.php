<?php

  /*!
   * This file contains PHP logic that is separated from the HTML view.
   * Visual changes can be made to the file found in the template folder:
   *
   *   ~/frontend/templates/default/partials/site_navigation.inc.php
   */

  $site_navigation = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/site_navigation.inc.php');

  $site_navigation_cache_token = cache::token('site_navigation', ['language']);
  if (!$site_navigation->snippets = cache::get($site_navigation_cache_token)) {

    $site_navigation->snippets = [
      'categories' => [],
      'brands' => [],
      'pages' => [],
      'shopping_cart' => [],
    ];

  // Categories

    $categories_query = functions::catalog_categories_query(0);

    while ($category = database::fetch($categories_query)) {
      $site_navigation->snippets['categories'][] = [
        'type' => 'category',
        'id' => $category['id'],
        'title' => $category['name'],
        'link' => document::ilink('category', ['category_id' => $category['id']]),
        'priority' => $category['priority'],
      ];
    }

  // Brands

    $pages_query = database::query(
      "select id, name from ". DB_TABLE_PREFIX ."brands
      where status
      and featured
      order by name;"
    );

    while ($brand = database::fetch($pages_query)) {
      $site_navigation->snippets['brands'][] = [
        'type' => 'brand',
        'id' => $brand['id'],
        'title' => $brand['name'],
        'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
        'priority' => 0,
      ];
    }

  // Pages

    $pages_query = database::query(
      "select p.id, p.priority, pi.title from ". DB_TABLE_PREFIX ."pages p
      left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where status
      and parent_id = 0
      and find_in_set('menu', dock)
      order by p.priority, pi.title;"
    );

    while ($page = database::fetch($pages_query)) {
      $site_navigation->snippets['pages'][] = [
        'type' => 'page',
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::ilink('page', ['page_id' => $page['id']]),
        'priority' => $page['priority'],
        'subitems' => [],
      ];

      $subpages_query = database::query(
        "select p.id, p.priority, pi.title from ". DB_TABLE_PREFIX ."pages p
        left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
        where status
        and parent_id = ". (int)$page['id'] ."
        order by p.priority, pi.title;"
      );

      while ($subpage = database::fetch($subpages_query)) {
        $site_navigation->snippets['pages'][] = [
          'type' => 'page',
          'id' => $subpage['id'],
          'title' => $subpage['title'],
          'link' => document::ilink('page', ['page_id' => $subpage['id']]),
          'priority' => $subpage['priority'],
        ];
      }
    }

  // Information

    $pages_query = database::query(
      "select p.id, p.priority, pi.title from ". DB_TABLE_PREFIX ."pages p
      left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where status
      and find_in_set('information', dock)
      order by p.priority, pi.title;"
    );

    while ($page = database::fetch($pages_query)) {
      $site_navigation->snippets['information'][] = [
        'type' => 'page',
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::ilink('information', ['page_id' => $page['id']]),
        'priority' => $page['priority'],
      ];
    }

    cache::set($site_navigation_cache_token, $site_navigation->snippets);
  }

// Shopping Cart

  $site_navigation->snippets['shopping_cart'] = [
    'items' => [],
    'link' => document::ilink('shopping_cart'),
    'num_items' => cart::$total['items'],
    'total' => null,
  ];

  foreach (cart::$items as $key => $item) {
    $item['image'] = 'storage://images/' . $item['image'];
    $site_navigation->snippets['shopping_cart']['items'][$key] = $item;
  }

  if (!empty(customer::$data['display_prices_including_tax'])) {
    $site_navigation->snippets['shopping_cart']['total'] = currency::format(cart::$total['value'] + cart::$total['tax']);
  } else {
    $site_navigation->snippets['shopping_cart']['total'] = currency::format(cart::$total['value']);
  }

  functions::draw_lightbox();

  echo $site_navigation->render();
