<?php

  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => 'define(\'DB_CONNECTION_CHARSET\', \'utf8\');',
        'replace' => 'define(\'DB_CONNECTION_CHARSET\', \'utf8mb4\');',
        'regex' => false,
      ],
    ]
  ]);

  // Switch the flawed 3-byte UTF8 implementation for full four-byte UTF8.
  // This is a safe operation because utf8mb4 is a superset of utf8.
  $database_info = database::query(
    "SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME 
    FROM information_schema.SCHEMATA
    WHERE schema_name = '". database::input(DB_DATABASE) ."'"
  )->fetch();

  $db_charset = $database_info['DEFAULT_CHARACTER_SET_NAME'];
  $db_collation = $database_info['DEFAULT_COLLATION_NAME'];

  $is_bad_charset = ($db_charset == 'utf8' || $db_charset == 'utf8mb3');

  if ($is_bad_charset || substr($db_collation, 0, 5) == 'utf8_' || substr($db_collation, 0, 8) == 'utf8mb3_') {
    // Update the database defaults if they're utf8_….
    // This is a compromise between the explicit `set_database_default` checkbox in Storage Encoding
    // and automatically migrating people. It will only trigger when it is safe, and won't
    // force UTF8 when LiteCart is in a shared database with other tables and a different
    // default character set.

    $new_charset = $is_bad_charset ? 'utf8mb4' : $db_charset;

    database::query(
      "ALTER DATABASE '". database::input(DB_DATABASE) ."'
      CHARACTER SET = ".$new_charset."
      COLLATE = ".str_replace(array('utf8_', 'utf8mb3_'), 'utf8mb4_', $db_charset).";"
    );
  }

  $tables_query = database::query(
    "SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = '". database::input(DB_DATABASE) ."'
    AND TABLE_NAME LIKE '". database::input(DB_TABLE_PREFIX) ."%'
    ORDER BY TABLE_NAME;"
  );

  while ($table = $tables_query->fetch()) {
    $collation = $table['TABLE_COLLATION'];
    if (substr($collation, 0, 5) != 'utf8_' && substr($collation, 0, 8) != 'utf8mb3_') {
      continue;
    }
    
    $new_collation = str_replace(array('utf8_', 'utf8mb3_'), 'utf8mb4_', $collation);
    database::query(
      "ALTER TABLE `". DB_DATABASE ."`.`". $table['TABLE_NAME'] ."`
      CONVERT TO CHARACTER SET ". database::input(preg_replace('#^([^_]+).*$#', '$1', $new_collation)) ."
      COLLATE ". database::input($new_collation) .";"
    );
  }