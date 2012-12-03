<?php

$app_config = array(
  'name' => $system->language->translate('title_settings', 'Settings'),
  'index' => 'settings.php',
  'params' => array(
    'setting_group_key' => 'store_info',
  ),
  'icon' => 'icon.png',
  'menu' => array(),
);

  $settings_groups_query = $system->database->query(
    "select * from ". DB_TABLE_SETTINGS_GROUPS ."
    order by priority, `key`;"
  );
  while ($group = $system->database->fetch($settings_groups_query)) {
    $app_config['menu'][] = array(
      'name' => $system->language->translate('settings_group:'.$group['key'], $group['name']),
      'link' => 'settings.php',
      'params' => array('setting_group_key' => $group['key']),
    );
  }

?>