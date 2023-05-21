<?php

  if (!empty($_GET['page_id'])) {
    $current_page_path = array_keys(reference::page($_GET['page_id'])->path);
  } else {
    $current_page_path = [];
  }

  $box_customer_service_links = new ent_view();

  $box_customer_service_links->snippets = [
    'title' =>  language::translate('title_customer_service', 'Customer Service'),
    'page_path' => $current_page_path,
    'pages' => [],
  ];

  $box_customer_service_links->snippets['pages'][] = [
    'id' => 0,
    'title' => language::translate('title_contact_us', 'Contact Us'),
    'link' => document::ilink('customer_service'),
    'opened' => false,
    'active' => (route::$route['page'] == 'customer_service' && empty($_GET['page_id'])) ? true : false,
    'subpages' => [],
  ];

  $iterator = function($parent_id, $level) use (&$iterator, &$current_page_path) {

    $output = [];

    $pages_query = database::query(
      "select p.id, p.parent_id, pi.title, p.priority, p.date_updated
      from ". DB_TABLE_PREFIX ."pages p
      left join ". DB_TABLE_PREFIX ."pages_info pi on (pi.page_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where p.status
      ". (!empty($parent_id) ? "and p.parent_id = ". (int)$parent_id ."" : "and find_in_set('customer_service', p.dock)") ."
      order by p.priority asc, pi.title asc;"
    );

    while ($page = database::fetch($pages_query)) {
      $output[$page['id']] = [
        'id' => $page['id'],
        'parent_id' => $page['parent_id'],
        'title' => $page['title'],
        'link' => document::ilink('customer_service', ['page_id' => $page['id']], false),
        'active' => (!empty($_GET['page_id']) && $page['id'] == $_GET['page_id']) ? true : false,
        'opened' => (!empty($current_page_path) && in_array($page['id'], $current_page_path)) ? true : false,
        'subpages' => [],
      ];

      if (in_array($page['id'], $current_page_path)) {
        $sub_pages_query = database::query(
          "select id from ". DB_TABLE_PREFIX ."pages
          where parent_id = ". (int)$page['id'] .";"
        );
        if (database::num_rows($sub_pages_query) > 0) {
          $output[$page['id']]['subpages'] = $iterator($page['id'], $level+1);
        }
      }
    }

    return $output;
  };

  $box_customer_service_links->snippets['pages'] = $iterator(0, 0);

  echo $box_customer_service_links->stitch('views/box_customer_service_links');
