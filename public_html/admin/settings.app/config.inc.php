<?php

$app_config = array(
  'name' => $GLOBALS['system']->language->translate('title_settings', 'Settings'),
  'default' => 'store_info',
  'menu' => array(),
  'icon' => 'icon.png',
  'docs' => array(),
);

  $settings_groups_query = $GLOBALS['system']->database->query(
    "select * from ". DB_TABLE_SETTINGS_GROUPS ."
    order by priority, `key`;"
  );
  while ($group = $GLOBALS['system']->database->fetch($settings_groups_query)) {
    $app_config['menu'][] = array(
      'title' => $GLOBALS['system']->language->translate('settings_group:title_'.$group['key'], $group['name']),
      'doc' => $group['key'],
      'params' => array(),
    );
    $app_config['docs'][$group['key']] = 'settings.inc.php';
  }

?>