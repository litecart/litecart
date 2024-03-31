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

    $site_navigation->snippets['categories'] = functions::catalog_categories_query(0)->fetch_custom(function($category) {
      return [
        'type' => 'category',
        'id' => $category['id'],
        'title' => $category['name'],
        'link' => document::ilink('category', ['category_id' => $category['id']]),
        'priority' => $category['priority'],
      ];
    });

  // Brands

  $site_navigation->snippets['brands'] = database::query(
      "select id, name from ". DB_TABLE_PREFIX ."brands
      where status
      and featured
      order by name;"
    )->fetch_custom(function($brand) {
      return [
        'type' => 'brand',
        'id' => $brand['id'],
        'title' => $brand['name'],
        'link' => document::ilink('brand', ['brand_id' => $brand['id']]),
        'priority' => 0,
      ];
    });

  // Pages

  $site_navigation->snippets['pages'] = database::query(
    "select p.id, p.priority, pi.title from ". DB_TABLE_PREFIX ."pages p
    left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
    where status
    and parent_id = 0
    and find_in_set('menu', dock)
    order by p.priority, pi.title;"
  )->fetch_custom(function($page) {
    return [
      'type' => 'page',
      'id' => $page['id'],
      'title' => $page['title'],
      'link' => document::ilink('page', ['page_id' => $page['id']]),
      'priority' => $page['priority'],
      'subitems' => database::query(
        "select p.id, p.priority, pi.title from ". DB_TABLE_PREFIX ."pages p
        left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
        where status
        and parent_id = ". (int)$page['id'] ."
        order by p.priority, pi.title;"
      )->fetch_custom(function($subpage) {
        return [
          'type' => 'page',
          'id' => $subpage['id'],
          'title' => $subpage['title'],
          'link' => document::ilink('page', ['page_id' => $subpage['id']]),
          'priority' => $subpage['priority'],
        ];
      }),
    ];
  });

  // Information

   $site_navigation->snippets['information'] = database::query(
      "select p.id, p.priority, pi.title from ". DB_TABLE_PREFIX ."pages p
      left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where status
      and find_in_set('information', dock)
      order by p.priority, pi.title;"
    )->fetch_custom(function($page) {
      return [
        'type' => 'page',
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::ilink('information', ['page_id' => $page['id']]),
        'priority' => $page['priority'],
      ];
    });

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
