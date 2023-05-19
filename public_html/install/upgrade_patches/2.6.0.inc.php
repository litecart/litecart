<?php

// Delete some files
  perform_action('delete', [
    FS_DIR_APP . 'ext/jquery/jquery-3.6.4.min.js',
  ], 'skip');

  // Get store timezone
  $setting_query = database::query(
    "SELECT * FROM ". DB_TABLE_PREFIX ."settings
    WHERE `key` = 'store_timezone'
    LIMIT 1;"
  );

  if ($timezone = database::fetch($setting_query, 'value')) {

    $datetime = new \DateTime('now', new \DateTimezone($timezone));

  // Get all timestamp columns in database
    $columns_query = database::query(
      "SELECT * FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = '". database::input(DB_DATABASE) ."'
      AND COLUMN_TYPE = 'timestamp';"
    );

    while ($column = database::fetch($columns_query)) {

    // Convert timestamps for column
      database::query(
        "UPDATE ". $column['TABLE_NAME'] ."
        SET `". $column['COLUMN_NAME'] ."` = CONVERT_TZ(`". $column['COLUMN_NAME'] ."`, @@GLOBAL.time_zone, '". $timezone->format('P') ."');"
      );
    }
  }
