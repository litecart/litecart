<?php
  $box_site_footer_cache_token = cache::token('box_site_footer', ['language', 'login', 'region']);
  if (cache::capture($box_site_footer_cache_token)) {

    $box_site_footer = new ent_view();

    $box_site_footer->snippets = [
      'pages' => [],
      'modules' => [],
      'social' => [],
    ];

    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where status
      and find_in_set('information', dock)
      order by p.priority, pi.title;"
    );

    while ($page = database::fetch($pages_query)) {
      $box_site_footer->snippets['pages'][$page['id']] = [
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::href_ilink('information', ['page_id' => $page['id']]),
      ];
    }

    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where status
      and find_in_set('customer_service', dock)
      order by p.priority, pi.title;"
    );

    while ($page = database::fetch($pages_query)) {
      $box_site_footer->snippets['customer_service_pages'][$page['id']] = [
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::href_ilink('customer_service', ['page_id' => $page['id']]),
      ];
    }

    $modules_query = database::query(
      "select id, settings  from ". DB_TABLE_MODULES ."
      where type in ('shipping', 'payment')
      and status
      order by type, id;"
    );

    while ($module = database::fetch($modules_query)) {
      $module['settings'] = json_decode($module['settings'], true);

      if (empty($module['settings']['icon'])) continue;
      if (!is_file(FS_DIR_APP . $module['settings']['icon'])) continue;

      $box_site_footer->snippets['modules'][$module['settings']['icon']] = [
        'id' => $module['id'],
        //'title' => $module['name'],
        'icon' => functions::image_thumbnail(FS_DIR_APP . $module['settings']['icon'], 72, 32, 'FIT_USE_WHITESPACING'),
      ];
    }

    $box_site_footer->snippets['social']['facebook'] = [
      'type' => 'facebook',
      'title' => 'Facebook',
      'icon' => 'fa-facebook',
      'link' => 'https://www.facebook.com/',
    ];

    $box_site_footer->snippets['social']['twitter'] = [
      'type' => 'twitter',
      'title' => 'Twitter',
      'icon' => 'fa-twitter',
      'link' => 'https://www.twitter.com/',
    ];

    $box_site_footer->snippets['social']['linkedin'] = [
      'type' => 'linkedin',
      'title' => 'LinkedIn',
      'icon' => 'fa-linkedin',
      'link' => 'https://www.linkedin.com/',
    ];

    echo $box_site_footer->stitch('views/box_site_footer');

    cache::end_capture($box_site_footer_cache_token);
  }
