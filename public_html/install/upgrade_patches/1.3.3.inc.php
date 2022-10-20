<?php

  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "  ini_set('display_errors', 'Off');" . PHP_EOL,
        'replace' => "  ini_set('display_startup_errors', 'Off');" . PHP_EOL
                   . "  ini_set('display_errors', 'Off');" . PHP_EOL,
      ],
      [
        'search'  => "    ini_set('display_errors', 'On');" . PHP_EOL,
        'replace' => "    ini_set('display_startup_errors', 'On');" . PHP_EOL
                   . "    ini_set('display_errors', 'On');" . PHP_EOL,
      ],
    ],
  ], 'abort');

// Rename module settings keys
  $query = database::query(
    "select * from ". DB_TABLE_PREFIX ."settings
    where (
      `key` like 'customer_module_%'
      or `key` like 'jobs_module_%'
      or `key` like 'shipping_module_%'
      or `key` like 'payment_module_%'
      or `key` like 'order_action_module_%'
      or `key` like 'order_success_module_%'
      or `key` like 'order_total_module_%'
    );"
  );

  while ($row = database::fetch($query)) {
    $new_key = preg_replace('#^((customer|jobs|shipping|payment|order_action|order_success|order_total)_module_)#', '', $row['key']);
    database::query(
      "update  ". DB_TABLE_PREFIX ."settings
      set `key` = '". database::input($new_key) ."'
      where `key` = '". database::input($row['key']) ."'
      limit 1;"
    );
  }
