<?php
  $site_footer_cache_token = cache::token('site_footer', ['language', 'login', 'region']);
  if (cache::capture($site_footer_cache_token)) {

    $site_footer = new ent_view(FS_DIR_TEMPLATE . 'partials/site_footer.inc.php');

    $site_footer->snippets = [
      'pages' => [],
      'modules' => [],
      'social_bookmarks' => [],
    ];

    $pages_query = database::query(
      "select p.id, pi.title from ". DB_TABLE_PREFIX ."pages p
      left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      where status
      and find_in_set('information', dock)
      order by p.priority, pi.title;"
    );

    while ($page = database::fetch($pages_query)) {
      $site_footer->snippets['pages'][$page['id']] = [
        'id' => $page['id'],
        'title' => $page['title'],
        'link' => document::href_ilink('information', ['page_id' => $page['id']]),
      ];
    }

    $modules_query = database::query(
      "select id, settings  from ". DB_TABLE_PREFIX ."modules
      where type in ('shipping', 'payment')
      and status
      order by type, id;"
    );

    while ($module = database::fetch($modules_query)) {
      $module['settings'] = json_decode($module['settings'], true);

      if (empty($module['settings']['icon'])) continue;
      $icon = 'app://'.$module['settings']['icon'];

      if (!is_file($icon)) continue;

      $site_footer->snippets['modules'][$module['id']] = [
        'id' => $module['id'],
        'icon' => functions::image_thumbnail($icon, 72, 32),
      ];
    }

    $social_media = [
      'facebook',
      'instagram',
      'linkedin',
      'pinterest',
      'twitter',
      'youtube',
    ];

    foreach ($social_media as $platform) {
      if (!$link = settings::get($platform.'_link')) continue;
      $site_footer->snippets['social_bookmarks'][$platform] = [
        'type' => $platform,
        'title' => ucfirst($platform),
        'icon' => 'fa-'.$platform,
        'link' => $link,
      ];
    }

    echo $site_footer;

    cache::end_capture($site_footer_cache_token);
  }
