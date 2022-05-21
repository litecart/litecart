<?php

  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => "RewriteCond %{REQUEST_URI} !^\\.well-known/",
        'replace' => "RewriteCond %{REQUEST_URI} !^/\\.well-known/",
      ],
    ],
  ], 'abort');

  $columns_query = database::query(
    "SELECT * FROM `information_schema`.COLUMNS
    WHERE TABLE_SCHEMA = '". DB_DATABASE ."'
    AND TABLE_NAME like '". DB_TABLE_PREFIX ."%'
    AND DATA_TYPE = 'decimal';"
  );

  while ($column = database::fetch($columns_query)) {
    database::query(
      "ALTER TABLE ". $column['TABLE_NAME'] ."
      CHANGE COLUMN `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". strtr($column['COLUMN_TYPE'], ['decimal' => 'float']) ." ". (($column['IS_NULLABLE'] == 'YES') ? "NULL" : "NOT NULL") ." ". ($column['COLUMN_DEFAULT'] ? "DEFAULT ". $column['COLUMN_DEFAULT'] : "") .";"
    );
  }
