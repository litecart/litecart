<?php

// Delete some files
  perform_action('delete', [
    FS_DIR_APP . 'ext/jquery/jquery-3.6.4.min.js',
    FS_DIR_ADMIN . 'translations.app/search.inc.php',
  ], 'skip');

  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => '  <FilesMatch "\.(a?png|bmp|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
        'replace' => '  <FilesMatch "\.(a?png|avif|bmp|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
        'regex'   => false,
      ],
    ],
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => '#'. preg_quote('// Database Tables - Backwards Compatibility (LiteCart <2.3)', '#') .'.*?\s*(\#*)#s',
        'replace' => '$1',
        'regex' => true,
      ],
    ],
  ], 'skip');

// Get store timezone
  if ($timezone = database::query(
    "SELECT * FROM ". DB_TABLE_PREFIX ."settings
    WHERE `key` = 'store_timezone'
    LIMIT 1;"
  )->fetch('value')) {

    $datetime = new \DateTime('now', new \DateTimezone($timezone));

  // Get all timestamp columns in database
    database::query(
      "SELECT * FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = '". database::input(DB_DATABASE) ."'
      AND COLUMN_TYPE = 'timestamp';"
    )->each(function($column) use ($datetime) {

    // Convert timestamps for column
      database::query(
        "UPDATE ". $column['TABLE_NAME'] ."
        SET `". $column['COLUMN_NAME'] ."` = CONVERT_TZ(`". $column['COLUMN_NAME'] ."`, @@GLOBAL.time_zone, '". $datetime->format('P') ."');"
      );
    });
  }

// Workaround for compatibility with both mysql and mariadb
  if (!database::query(
    "SELECT * FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = '". database::input(DB_SERVER) ."'
    AND TABLE_NAME = '". database::input(DB_TABLE_PREFIX . 'users') ."'
    AND COLUMN_NAME = 'date_expire_sessions'
    LIMIT 1;"
  )->num_rows) {

    database::query(
      "ALTER TABLE ". DB_TABLE_PREFIX ."users
      ADD COLUMN `date_expire_sessions` TIMESTAMP NULL AFTER `date_login`;"
    );
  }
