<?php

  $columns_query = database::query(
    "SELECT * FROM `information_schema`.COLUMNS
    WHERE TABLE_SCHEMA = '". DB_DATABASE ."'
    AND TABLE_NAME like '". DB_TABLE_PREFIX ."%'
    AND DATA_TYPE = 'double';"
  );

  while ($column = database::fetch($columns_query)) {
    database::query(
      "ALTER TABLE ". $column['TABLE_NAME'] ."
      CHANGE COLUMN `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". strtr($column['COLUMN_TYPE'], ['double' => 'float']) ." ". (($column['IS_NULLABLE'] == 'YES') ? "NULL" : "NOT NULL") ." ". ($column['COLUMN_DEFAULT'] ? "DEFAULT ". $column['COLUMN_DEFAULT'] : "") .";"
    );
  }
