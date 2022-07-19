<?php

  $app_config = [
    'name' => language::translate('title_settings', 'Settings'),
    'default' => 'store_info',
    'priority' => 0,

    'theme' => [
      'color' => '#757575',
      'icon' => 'fa-cogs',
    ],

    'menu' => [],
    'docs' => [],
  ];

  $settings_groups_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."settings_groups
    order by priority, `key`;"
  );

  while ($group = database::fetch($settings_groups_query)) {
    $app_config['menu'][] = [
      'title' => language::translate('settings_group:title_'.$group['key'], $group['name']),
      'doc' => $group['key'],
      'params' => [],
    ];
    $app_config['docs'][$group['key']] = 'settings.inc.php';
  }

  return $app_config;
