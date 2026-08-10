<?php

	### Database > Load Structure #######################################

	// Fetch MySQL table structures from structure.json
	$structure_file = __DIR__ . '/../structure.json';

	if (!is_file($structure_file)) {
		throw new Exception('Could not find structure.json');
	}

	// Read structure.json
	$structure = file_get_contents($structure_file);

	// Set table prefixes
	$structure = str_replace('"table": "', '"table": "'. DB_TABLE_PREFIX, $structure);

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
		$structure['tables'][$key]['name'] = DB_TABLE_PREFIX . $key;

		// Assign table prefix to foreign key references
		if (!empty($table['foreign_keys'])) {
			foreach ($table['foreign_keys'] as $fk_key => $fk) {
				$structure['tables'][$key]['foreign_keys'][$fk_key]['references']['table'] = DB_TABLE_PREFIX . $fk['references']['table'];
			}
		}
	}

	### Database > Clean #######################################

	echo '<p>Cleaning database... ';

	// Iterate through tables and drop them
	foreach ($structure['tables'] as $table) {
		database::query(
			"DROP TABLE IF EXISTS `". database::input($table['name']) ."`;"
		);
	}

	echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	### Database > Tables > Structure #############################

	echo '<p>Writing database tables... ';

	// Iterate through tables
	foreach ($structure['tables'] as $table) {

		// Check if table contains any columns
		if (empty($table['columns'])) {
			throw new Exception('Table ' . $table['name'] . ' does not contain any columns.');
		}

		// Create SQL statement
		$sql = 'CREATE TABLE `' . database::input($table['name']) . '` (' . PHP_EOL;

		foreach ($table['columns'] as $column_name => $column) {

			$sql .= '  `' . $column_name . '` ' . $column['type'];

			if (isset($column['length']) && strpos($column['type'], '(') === false) {
				$sql .= '(' . $column['length'] . ')';
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
				$sql .= ' ON UPDATE ' . $column['on_update'];
			}

			$sql .= ', ' . PHP_EOL;
		}

		// Create primary key
		if (isset($table['primary_key'])) {
			$sql .= '  PRIMARY KEY (`' . implode('`, `', database::input($table['primary_key'])) . '`),' . PHP_EOL;
		}

		// Create unique keys
		if (isset($table['unique_keys'])) {
			foreach ($table['unique_keys'] as $key_name => $key_columns) {
				$sql .= '  UNIQUE KEY `' . database::input($key_name) . '` (`' . implode('`, `', database::input($key_columns)) . '`),' . PHP_EOL;
			}
		}

		// Create fulltext keys
		if (isset($table['fulltext_keys'])) {
			foreach ($table['fulltext_keys'] as $key_name => $key_columns) {
				$sql .= '  FULLTEXT KEY `' . database::input($key_name) . '` (`' . implode('`, `', database::input($key_columns)) . '`),' . PHP_EOL;
			}
		}

		// Create keys
		if (isset($table['keys'])) {
			foreach ($table['keys'] as $key_name => $key_columns) {
				$sql .= '  KEY `' . database::input($key_name) . '` (`' . implode('`, `', database::input($key_columns)) . '`),' . PHP_EOL;
			}
		}

		// Create check constraints
		if (!empty($table['check_constraints'])) {
			foreach ($table['check_constraints'] as $name => $expression) {
				$sql .= 'CONSTRAINT `'. $name .'` CHECK ('. $expression .'),' . PHP_EOL;
			}
		}

		$sql = rtrim($sql, ', ' . PHP_EOL) . PHP_EOL . ')';

		if (isset($table['engine'])) {
			$sql .= ' ENGINE=' . database::input($table['engine']);
		} else {
			$sql .= ' ENGINE=InnoDB';
		}

		if (isset($table['charset'])) {
			$sql .= ' DEFAULT CHARSET=' . database::input($table['charset']);
		} else {
			$sql .= ' DEFAULT CHARSET=utf8mb4';
		}

		if (!empty($_REQUEST['db_collation'])) {
			$sql .= ' COLLATE=' . $_REQUEST['db_collation'];
		} else if (isset($table['collation'])) {
			$sql .= ' COLLATE=' . database::input($table['collation']);
		} else {
			$sql .= ' COLLATE=utf8mb4_unicode_ci';
		}

		$sql .= ';';

		// Workaround for early MySQL versions (<5.6.5) not supporting multiple DEFAULT CURRENT_TIMESTAMP
		if (version_compare($database_software['version'], '5.6.5', '<')) {
			str_replace('`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,', '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,', $sql);
		}

		// Execute SQL statement
		database::query($sql);
	}

	echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	### Database > Tables > Data ##################################

	if ($data_files = glob(__DIR__.'/../data/default/*.csv')) {

		echo '<p>Writing database table data from CSV files... ' . PHP_EOL;

		foreach ($data_files as $file) {

			$table = DB_TABLE_PREFIX . basename($file, '.csv');

			$contents = file_get_contents($file);

			foreach ([
				'{STORE_NAME}' => isset($_REQUEST['store_name']) ? $_REQUEST['store_name'] : '',
				'{STORE_EMAIL}' => isset($_REQUEST['store_email']) ? $_REQUEST['store_email'] : '',
				'{STORE_COUNTRY_CODE}' => isset($_REQUEST['country_code']) ? $_REQUEST['country_code'] : '',
				'CURRENT_TIMESTAMP' => date('Y-m-d H:i:s'),
			] as $search => $replace) {
				$contents = str_replace($search, database::input($replace), $contents);
			}

			$rows = f::csv_decode($contents);

			$query = "INSERT INTO `". database::input($table) ."` (`". implode('`, `', database::input(array_keys($rows[0]))) ."`) VALUES ";

			foreach ($rows as $columns) {
				$query .= "('". implode("', '", database::input($columns, true)) ."'),";
			}

			$query = rtrim($query, ',') . ";";

			echo 'Importing '. basename($file) .'... ';
			database::query($query);

			echo '<span class="ok">[OK]</span></p>' . PHP_EOL;
		}

		echo PHP_EOL;
	}

	if (file_exists(__DIR__.'/../data/default/data.sql')) {

		echo '<p>Writing database table data from SQL file... ';

		$sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, file_get_contents(__DIR__.'/../data/default/data.sql'));

		foreach ([
			'{STORE_NAME}' => isset($_REQUEST['store_name']) ? $_REQUEST['store_name'] : '',
			'{STORE_EMAIL}' => isset($_REQUEST['store_email']) ? $_REQUEST['store_email'] : '',
			'{STORE_COUNTRY_CODE}' => isset($_REQUEST['country_code']) ? $_REQUEST['country_code'] : '',
		] as $search => $replace) {
			$sql = str_replace($search, database::input($replace), $sql);
		}

		foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
			$query = preg_replace('#^-- .*?\R+#m', '', $query);
			database::query($query);
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	}

	### Admin > Database > Administrators ##################################

	database::query(
		"insert into `". DB_TABLE_PREFIX ."administrators`
		(`id`, `status`, `username`, `password_hash`, `known_ips`, `updated_at`, `created_at`)
		values ('1', '1', '". database::input($_REQUEST['username']) ."', '". database::input(password_hash($_REQUEST['password'], PASSWORD_DEFAULT)) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
	);

	### Set platform database version #############################

	echo '<p>Set platform database version...';

	if (defined('PLATFORM_VERSION')) {

		database::query(
			"update `". DB_TABLE_PREFIX ."settings`
			set `value` = '". database::input(PLATFORM_VERSION) ."'
			where `key` = 'platform_database_version'
			limit 1;"
		);

		echo ' <strong>'. PLATFORM_VERSION .'</strong></p>' . PHP_EOL . PHP_EOL;

	} else {
		echo ' <span class="error">[Error: Not defined]</span></p>' . PHP_EOL . PHP_EOL;
	}
