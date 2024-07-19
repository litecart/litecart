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
				database::query(
					"alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
					change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". strtok($column['COLUMN_TYPE'], ' ') ." unsigned not null auto_increment;"
				);
				break;

			case 'date_updated':
			case 'date_created':
				database::query(
					"alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
					change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` timestamp not null default current_timestamp;"
				);
				break;

			case 'date_accessed':
			case 'date_active':
			case 'date_expires':
			case 'date_hit':
			case 'date_login':
			case 'date_processed':
			case 'date_published':
			case 'date_pushed':
			case 'date_redirected':
			case 'date_scheduled':
			case 'date_sent':
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

				switch(strtolower($column['DATA_TYPE'])) {
					case 'int':
					case 'decimal':
					case 'tinyint':
					case 'smallint':
					case 'mediumint':
					case 'bigint':
						database::query(
							"alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
							change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". $column['COLUMN_TYPE'] ." not null default ". (!empty($column['COLUMN_DEFAULT'] && strtolower($column['COLUMN_DEFAULT']) != 'null') ? $column['COLUMN_DEFAULT'] : "'0'") .";"
						);
						break;

					case 'float':
						database::query(
							"alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
							change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` decimal(11,4) not null default ". (!empty($column['COLUMN_DEFAULT'] && strtolower($column['COLUMN_DEFAULT']) != 'null') ? $column['COLUMN_DEFAULT'] : "'0'") .";"
						);
						break;

					case 'datetime':
						database::query(
							"alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
							change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` timestamp null default ". (!empty($column['COLUMN_DEFAULT'] && strtolower($column['COLUMN_DEFAULT']) != 'null') ? $column['COLUMN_DEFAULT'] : "null") .";"
						);
						break;

					default:
						database::query(
							"alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
							change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". $column['COLUMN_TYPE'] ." not null default '". (!empty($column['COLUMN_DEFAULT'] && strtolower($column['COLUMN_DEFAULT']) != 'null') ? trim($column['COLUMN_DEFAULT'], "'") : "") ."';"
						);
						break;
				}

				break;
		}
	}

	// Set language url type
	$seo_links_language_prefix = database::query(
		"select `value` from ". DB_TABLE_PREFIX ."settings
		where `key` = 'seo_links_language_prefix'
		limit 1;"
	)->fetch('value');

	if ($seo_links_language_prefix) {
		database::query(
			"update ". DB_TABLE_PREFIX ."languages
			set url_type = 'path';"
		);
	} else {
		database::query(
			"update ". DB_TABLE_PREFIX ."languages
			set url_type = 'none';"
		);
	}
