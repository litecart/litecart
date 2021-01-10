<?php

// Adjust tables
  $columns_query = database::query(
    "select * from information_schema.COLUMNS
    where TABLE_SCHEMA = '". DB_DATABASE ."'
    and TABLE_NAME like '". DB_TABLE_PREFIX ."%';"
  );

  while ($column = database::fetch($columns_query)) {
    switch ($column['COLUMN_NAME']) {
      case 'id':
        break;

      case 'date_updated':
      case 'date_created':
        database::query(
          "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
          change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` timestamp not null default current_timestamp;"
        );
        break;

      case 'date_active':
      case 'date_login':
      case 'date_scheduled':
      case 'date_sent':
      case 'date_pushed':
      case 'date_processed':
      case 'date_valid_from':
      case 'date_valid_to':
      case 'start_date':
      case 'end_date':
        database::query(
          "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
          change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` timestamp null;"
        );
        break;

      default:

        switch(strtolower($column['COLUMN_TYPE'])) {
          case 'int':
          case 'decimal':
          case 'tinyint':
          case 'smallint':
          case 'mediumint':
          case 'bigint':
            database::query(
              "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
              change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". $column['COLUMN_TYPE'] ." not null". (!empty($column['COLUMN_DEFAULT']) ? ' default ' . $column['COLUMN_DEFAULT'] : '0') .";"
            );
            break;

          case 'float':
            database::query(
              "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
              change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` decimal(11,4) not null". (!empty($column['COLUMN_DEFAULT']) ? ' default ' . $column['COLUMN_DEFAULT'] : '0') .";"
            );
            break;

          default:
            database::query(
              "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
              change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". $column['COLUMN_TYPE'] ." not null". (!empty($column['COLUMN_DEFAULT']) ? ' default ' . $column['COLUMN_DEFAULT'] : '') .";"
            );
            break;
        }

        break;
    }
  }
