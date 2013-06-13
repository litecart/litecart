<?php

$app_config = array(
  'name' => $system->language->translate('title_settings', 'Settings'),
  'default' => 'store_info',
  'menu' => array(),
  'icon' => 'icon.png',
  'docs' => array(),
);

  $settings_groups_query = $system->database->query(
    "select * from ". DB_TABLE_SETTINGS_GROUPS ."
    order by priority, `key`;"
  );
  while ($group = $system->database->fetch($settings_groups_query)) {
    $app_config['menu'][] = array(
      'title' => $system->language->translate('title_settings_group:'.$group['key'], $group['name']),
      'doc' => $group['key'],
      'params' => array(),
    );
    $app_config['docs'][$group['key']] = 'settings.inc.php';
  }

?>