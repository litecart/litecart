<?php

	### Update Database Structure ##########################################

	try {

		echo 'Update table structures...' . PHP_EOL;

		// Get all existing tables
		$existing_tables = database::query(
			"select table_name from information_schema.tables
			where table_schema = '". DB_DATABASE ."';"
		)->fetch_all('table_name');

		$default_collation = database::query(
			"SELECT DEFAULT_COLLATION_NAME
			FROM INFORMATION_SCHEMA.SCHEMATA
			WHERE SCHEMA_NAME = '". database::input(DB_DATABASE) ."';"
		)->fetch('DEFAULT_COLLATION_NAME');

		// Fetch MySQL table structures from structure.json
		$structure_file = __DIR__ . '/../../structure.json';

		if (!is_file($structure_file)) {
			throw new Exception('Could not find structure.json');
		}

		// Read structure.json
		$structure = file_get_contents($structure_file);

		// Set table prefixes
		$structure = str_replace('"table": "', '"table": "'. DB_PREFIX, $structure);

		// Decode database structure
		$structure = json_decode($structure, true);

		// Check if structure.json could be decoded
		if ($structure === null) {
			throw new Exception('structure.json could not be decoded: ' . json_last_error_msg());
		}

		// Check if structure.json does not contain any tables
		if (empty($structure['tables'])) {
			throw new Exception('structure.json does not contain any tables.');
		}

		// Assign table name with table prefix
		foreach ($structure['tables'] as $key => $table) {
			$structure['tables'][$key]['name'] = DB_PREFIX . $key;

			// Assign table prefix to foreign key references
			if (!empty($table['foreign_keys'])) {
				foreach ($table['foreign_keys'] as $fk_key => $fk) {
					$structure['tables'][$key]['foreign_keys'][$fk_key]['references']['table'] = DB_PREFIX . $fk['references']['table'];
				}
			}
		}

	} catch (Throwable $t) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $t->getMessage() .'</div></p>',
			'',
			'',
		]);
	}

	### Ensure Specific Table Properties ###################################

	// Iterate through each table (this is to ensure specific table properties)
	foreach ($structure['tables'] as $table) {

		// If table exists
		if (in_array($table['name'], $existing_tables)) {

			// Get existing table properties
			$existing_table = database::query(
				"SHOW TABLE STATUS LIKE '". database::input($table['name']) ."';"
			)->fetch();

			// Convert engine and row format if needed
			if ($existing_table['Engine'] != 'InnoDB' || $existing_table['Row_format'] != 'Dynamic') {
				database::query(
					"ALTER TABLE `". database::input($table['name']) ."`
					ENGINE='InnoDB' ROW_FORMAT=DYNAMIC;"
				);
			}

			// Convert charset and collation if needed
			if ($existing_table['Create_options'] != 'CHARSET=utf8mb4' || $existing_table['Collation'] != $default_collation) {
				database::query(
					"ALTER TABLE `". database::input($table['name']) ."`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE ". database::input($default_collation) .";"
				);
			}
		}
	}

	### Modify Table Columns ############################################

	// Iterate through each table and add/change columns and keys
	foreach ($structure['tables'] as $table) {

		try {

			if (empty($table['columns'])) {
				throw new Exception('Table structure for '. $table['name'] .' in structure.json does not contain any columns');
			}

			if (in_array($table['name'], $existing_tables)) {
				$table_exists = true;
			} else {
				$table_exists = false;
			}

			if ($table_exists) {
				$sql = "ALTER TABLE `". database::input($table['name']) ."`" . PHP_EOL;
			} else {
				$sql = "CREATE TABLE `". database::input($table['name']) ."` (" . PHP_EOL;
			}

			$last_column = null;

			// Drop primary key
			if ($table_exists && !empty($table['primary_key'])) {
				if (database::query(
					"SHOW INDEX FROM `". database::input($table['name']) ."`
					WHERE Key_name = 'PRIMARY';"
				)->num_rows) {
					$sql .= 'DROP PRIMARY KEY,' . PHP_EOL;
				}
			}

			// Drop unique keys
			if ($table_exists && !empty($table['unique_keys'])) {
				foreach (array_keys($table['unique_keys']) as $key_name) {
					if (database::query(
						"SHOW INDEX FROM `". $table['name'] ."`
						WHERE Key_name = '". database::input($key_name) ."'
						AND non_unique = 0;"
					)->num_rows) {
						$sql .= "DROP INDEX `". database::input($key_name) ."`," . PHP_EOL;
					}
				}
			}

			// Drop keys
			if ($table_exists && !empty($table['keys'])) {
				foreach (array_keys($table['keys']) as $key_name) {
					if (database::query(
						"SHOW INDEX FROM `". database::input($table['name']) ."`
						WHERE Key_name = '". database::input($key_name) ."'
						AND non_unique = 1;"
					)->num_rows) {
						$sql .= 'DROP INDEX `'. database::input($key_name) .'`,' . PHP_EOL;
					}
				}
			}

			// Drop fulltext keys
			if ($table_exists && !empty($table['fulltext_keys'])) {
				foreach (array_keys($table['fulltext_keys']) as $key_name) {
					if (database::query(
						"SHOW INDEX FROM `". database::input($table['name']) ."`
						WHERE Key_name = '". database::input($key_name) ."'
						AND Index_type = 'FULLTEXT';"
					)->num_rows) {
						$sql .= 'DROP INDEX `'. database::input($key_name) .'`,' . PHP_EOL;
					}
				}
			}

			// Drop check constraints
			if ($table_exists && !empty($table['check_constraints'])) {
				foreach (array_keys($table['check_constraints']) as $name) {
					if (database::query(
						"SELECT CONSTRAINT_NAME
						FROM information_schema.table_constraints
						WHERE CONSTRAINT_SCHEMA = '". database::input(DB_DATABASE) ."'
						AND TABLE_NAME = '". database::input($table['name']) ."'
						AND CONSTRAINT_NAME = '". database::input($name) ."';"
					)->num_rows) {
						$sql .= 'DROP CONSTRAINT `'. database::input($name) .'`,' . PHP_EOL;
					}
				}
			}

			// Add/change columns
			foreach ($table['columns'] as $column_name => $column) {

				if ($table_exists && database::query(
					"SHOW COLUMNS FROM `". database::input($table['name']) ."`
					LIKE '". database::input($column_name) ."';"
				)->num_rows) {
					$column_exists = true;
				} else {
					$column_exists = false;
				}

				if ($table_exists) {

					if ($column_exists) {
						$sql .= 'CHANGE COLUMN `'. database::input($column_name) .'` `'. database::input($column_name) .'` '. $column['type'];
					} else {
						$sql .= 'ADD COLUMN `'. database::input($column_name) .'` '. $column['type'];
					}

				} else {
					$sql .= '  `'. database::input($column_name) .'` '. $column['type'];
				}

				if (isset($column['length']) && strpos($column['type'], '(') === false) {
					$sql .= '('. $column['length'] .')';
				}

				if (isset($column['unsigned']) && $column['unsigned'] === true) {
					$sql .= ' UNSIGNED';
				}

				if (!empty($column['nullable'])) {
					$sql .= ' NULL';
				} else {
					$sql .= ' NOT NULL';
				}

				if (isset($column['auto_increment']) && $column['auto_increment'] === true) {
					$sql .= ' AUTO_INCREMENT';
				}

				if (isset($column['default'])) {
					// MySQL 8.0+ requires parentheses around DEFAULT for BLOB/TEXT/JSON/GEOMETRY columns
					$blob_text = in_array(strtoupper($column['type']), ['TEXT', 'TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT', 'BLOB', 'TINYBLOB', 'MEDIUMBLOB', 'LONGBLOB', 'JSON', 'GEOMETRY']);
					$sql .= ' DEFAULT '. ($blob_text ? '('. $column['default'] .')' : $column['default']);
				}

				if (!empty($column['on_update'])) {
					$sql .= ' ON UPDATE '. $column['on_update'];
				}

				if ($table_exists && !$column_exists && $last_column) {
					$sql .= ' AFTER `'. $last_column .'`';
				}

				$sql .= ',' . PHP_EOL;

				$last_column = $column_name;
			}

			// Create primary key
			if (!empty($table['primary_key'])) {
				if ($table_exists) {
					$sql .= 'ADD PRIMARY KEY (`'. implode('`, `', database::input($table['primary_key'])) .'`),' . PHP_EOL;
				} else {
					$sql .= '  PRIMARY KEY (`'. implode('`, `', database::input($table['primary_key'])) .'`),' . PHP_EOL;
				}
			}

			// Create unique keys
			if (!empty($table['unique_keys'])) {
				foreach ($table['unique_keys'] as $key_name => $key_columns) {
					if ($table_exists) {
						$sql .= 'ADD CONSTRAINT `'. database::input($key_name) .'` UNIQUE (`'. implode('`, `', database::input($key_columns)) .'`),' . PHP_EOL;
					} else {
						$sql .= '  CONSTRAINT `'. database::input($key_name) .'` UNIQUE (`'. implode('`, `', database::input($key_columns)) .'`),' . PHP_EOL;
					}
				}
			}

			// Create fulltext keys
			if (isset($table['fulltext_keys'])) {
				foreach ($table['fulltext_keys'] as $key_name => $key_columns) {
					if ($table_exists) {
						$sql .= 'ADD FULLTEXT KEY `'. database::input($key_name) .'` (`'. implode('`, `', database::input($key_columns)) .'`),' . PHP_EOL;
					} else {
						$sql .= '  FULLTEXT KEY `' . database::input($key_name) . '` (`' . implode('`, `', database::input($key_columns)) . '`),' . PHP_EOL;
					}
				}
			}

			// Create keys
			if (!empty($table['keys'])) {
				foreach ($table['keys'] as $key_name => $key_columns) {
					if ($table_exists) {
						$sql .= 'ADD KEY `'. database::input($key_name) .'` (`'. implode('`, `', database::input($key_columns)) .'`),' . PHP_EOL;
					} else {
						$sql .= '  KEY `'. database::input($key_name) .'` (`'. implode('`, `', database::input($key_columns)) .'`),' . PHP_EOL;
					}
				}
			}

			// Create check constraints
			if (!empty($table['check_constraints'])) {
				foreach ($table['check_constraints'] as $name => $expression) {
					if ($table_exists) {
						$sql .= 'ADD CONSTRAINT `'. database::input($name) .'` CHECK ('. $expression .'),' . PHP_EOL;
					} else {
						$sql .= '  CONSTRAINT `'. database::input($name) .'` CHECK ('. $expression .'),' . PHP_EOL;
					}
				}
			}

			if ($table_exists) {
				$sql = rtrim($sql, ", \r\n") . ';';
			} else {
				$sql = rtrim($sql, ", \r\n") . PHP_EOL . ") ENGINE='InnoDB' ROW_FORMAT=DYNAMIC DEFAULT CHARSET='utf8mb4' COLLATE='". database::input($default_collation) ."';";
			}

			database::query($sql);

			echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} catch (Throwable $t) {
			echo implode(PHP_EOL, [
				'<span class="error">[Error]</span>',
				'<div class="error-message">'. $t->getMessage() .'</div></p>',
				'',
				'',
			]);
		}
	}
