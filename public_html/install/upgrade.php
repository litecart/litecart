<?php

	/*!
	 * Unattended Upgrade:
	 *  upgrade.php?upgrade=true&redirect={url}
	*/

	ini_set('memory_limit', -1);
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');
	@set_time_limit(900);

	require_once __DIR__ . '/../includes/compatibility.inc.php';

	if ($_SERVER['SERVER_SOFTWARE'] == 'CLI') {

		if (!isset($argv[1]) || (in_array($argv[1], ['help', '-h', '--help', '/?']))) {
			echo implode(PHP_EOL, [
				'',
				'LiteCart® 3.0.0',
				'Copyright (c) '. date('Y') .' LiteCart AB',
				'https://www.litecart.net/',
				'Usage: php '. basename(__FILE__) .' [options]',
				'',
				'Options:',
				'  --from_version       Manually set version migrating from. Omit for auto detection',
				'  --development_type   Set development type "standard" or "development" (Default: standard)',
				'  --backup             Backup the database before running upgrade (Default: true)',
				'  --cleanup            Delete the install/ directory after finishing the upgrade.',
				'',
			]);
			exit;
		}

		$options = [
			'from_version::',
			'development_type::',
			'backup::',
			'cleanup::',
		];

		$_REQUEST = getopt('', $options);
		$_REQUEST['upgrade'] = true;

		if (isset($_REQUEST['cleanup'])) {
			$_REQUEST['cleanup'] = true;
		}
	}

	// Include config
	if (is_file(__DIR__ . '/../storage/config.inc.php')) {
		include(__DIR__ . '/../storage/config.inc.php'); // 3.0.0+

	} else if (is_file(__DIR__ . '/../includes/config.inc.php')) { // Prior to 3.x
		include(__DIR__ . '/../includes/config.inc.php');

	} else {

		require_once __DIR__ . '/includes/header.inc.php';

		echo implode(PHP_EOL, [
			'<h2>No Installation Detected</h2>',
			'<p>Warning: No configuration file was found.</p>',
			'<p><a class="btn btn-default" href="index.php">Click here to install instead</a></p>',
		]);

		require_once 'includes/footer.inc.php';
		return;
	}

	error_reporting(E_ALL);
	ini_set('ignore_repeated_errors', 'On');
	ini_set('log_errors', 'Off');
	ini_set('display_errors', 'On');
	ini_set('html_errors', 'On');

	if ($_SERVER['SERVER_SOFTWARE'] != 'CLI') {
		ini_set('display_errors', 'Off');
		require_once __DIR__ . '/includes/header.inc.php';
	}

	require_once __DIR__ . '/includes/functions.inc.php';

	if (!defined('FS_DIR_APP')) {
		define('FS_DIR_APP', FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME); // Prior to 2.2.x
	}

	if (!defined('FS_DIR_STORAGE')) {
		define('FS_DIR_STORAGE', FS_DIR_APP . 'storage/'); // Prior to 3.x
	}

	if (!defined('WS_DIR_STORAGE')) {
		define('WS_DIR_STORAGE', WS_DIR_APP. 'storage/'); // Prior to 2.5.x
	}

	require_once FS_DIR_APP . 'includes/error_handler.inc.php';
	require_once FS_DIR_APP . 'includes/functions/func_file.inc.php';
	require_once FS_DIR_APP . 'includes/nodes/nod_database.inc.php';
	require_once FS_DIR_APP . 'includes/nodes/nod_event.inc.php';
	require_once FS_DIR_APP . 'includes/nodes/nod_functions.inc.php';
	require FS_DIR_APP . 'includes/nodes/nod_stats.inc.php';

	$requirements = json_decode(file_get_contents(__DIR__ . '/requirements.json'), true);

	// Set platform name
	preg_match('#define\(\'PLATFORM_NAME\', \'([^\']+)\'\);#', file_get_contents(__DIR__.'/../includes/app_header.inc.php'), $matches);
	define('PLATFORM_NAME', isset($matches[1]) ? $matches[1] : false);

	// Set platform version
	preg_match('#define\(\'PLATFORM_VERSION\', \'([^\']+)\'\);#', file_get_contents(__DIR__.'/../includes/app_header.inc.php'), $matches);
	define('PLATFORM_VERSION', isset($matches[1]) ? $matches[1] : false);

	if (!PLATFORM_VERSION) {
		die('Could not identify target version.');
	}

	// Get current platform database version
	$platform_database_version = database::query(
		"select `value` from ". DB_TABLE_PREFIX ."settings
		where `key` = 'platform_database_version'
		limit 1;"
	)->fetch('value');

	define('PLATFORM_DATABASE_VERSION', $platform_database_version);

	// List supported upgrades
	$supported_versions = ['1.0' => '1.0'];
	foreach (glob(__DIR__ . '/migrations/*') as $file) {
		if (preg_match('#/([^/]+).(?:inc.php|sql)$#', $file, $matches)) {
			$supported_versions[$matches[1]] = $matches[1];
		}
	}

	usort($supported_versions, function($a, $b) {
		return version_compare($a, $b, '>') ? 1 : -1;
	});

	if (empty($_REQUEST['development_type'])) {
		if (is_file($file = FS_DIR_APP . 'frontend/templates/default/.development')) {
			$_REQUEST['development_type'] = file_get_contents($file);
		}
	}

	if (!empty($_REQUEST['upgrade'])) {

		ob_start(function($buffer) {

			if ($_SERVER['SERVER_SOFTWARE'] == 'CLI') {
				$buffer = strip_tags($buffer);
			}

			return $buffer;
		});

		try {

			ignore_user_abort(true);

			echo '<h1>Upgrade '. PLATFORM_VERSION .'</h1>' . PHP_EOL . PHP_EOL;

			### PHP > Check Version ################################################

			echo '<p>Checking PHP version... ';

			if (version_compare(PHP_VERSION, $requirements['scripting']['php']['minimumVersion'], '<')) {
				throw new Exception(PHP_VERSION .' <span class="error">[Error] PHP '. $requirements['scripting']['php']['minimumVersion'].'+ minimum requirement</span></p>' . PHP_EOL . PHP_EOL);

			} else if (version_compare(PHP_VERSION, '7.2', '<=')) {
				echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' has reached <a href="https://www.php.net/supported-versions.php" target="_blank">end of life</a>.</span></p>' . PHP_EOL . PHP_EOL;

			} else if (version_compare(PHP_VERSION, $requirements['scripting']['php']['recommendedVersion'], '<=')) {
				echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' is below the recommended version '. $requirements['scripting']['php']['recommendedVersion'] .'+</p>' . PHP_EOL . PHP_EOL;

			} else {
				echo PHP_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
			}

			### PHP > Check PHP Extensisons ########################################

			echo '<p>Checking for PHP extensions... ';

			$missing_extensions = [];

			foreach ($requirements['scripting']['php']['requiredExtensions'] as $extension) {
				if ((is_array($extension) && !in_array(true, array_map(function($ext) {
					return extension_loaded($ext);
				}, $extension)) && !extension_loaded($extension))) {
					$missing_extensions[] = $extension;
				}
			}

			$missing_extensions = array_map(function($extension) {
				return is_array($extension) ? implode(' or ', $extension) : $extension;
			}, $missing_extensions);

			if ($missing_extensions) {
				echo '<span class="warning">[Warning] Some important PHP extensions are missing ('. implode(', ', $missing_extensions) .'). It is recommended that you enable them in php.ini.</span></p>' . PHP_EOL . PHP_EOL;
			} else {
				echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
			}

			### Database > Check Version ###########################################

			$database_software = database::query(
				"SELECT VERSION();"
			)->fetch(function($row) use ($requirements) {
				if (preg_match('#mariadb#i', $row['VERSION()'])) {
					return [
						'name' => 'MariaDB',
						'version' => strtok($row['VERSION()'], '-'),
						'min_version' => $requirements['databases']['mariadb']['minimumVersion'],
						'recommended_version' => $requirements['databases']['mariadb']['recommendedVersion'],
					];
				}
				return [
					'name' => 'MySQL',
					'version' => $row['VERSION()'],
					'min_version' => $requirements['databases']['mysql']['minimumVersion'],
					'recommended_version' => $requirements['databases']['mysql']['recommendedVersion'],
				];
			});

			echo $database_software['name'] .' '. $database_software['version'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

			echo '<p>Checking database software... ';

			if (version_compare($database_software['version'], $database_software['min_version'], '<')) {
				throw new Exception($database_software['name'] .' '. $database_software['version'] . ' <span class="error">[Error] '.  $database_software['name'] .' '. $database_software['min_version'] .'+ required</span></p>');

			} else if (version_compare($database_software['version'], $database_software['recommended_version'], '<')) {
				echo $database_software['name'] .' '. $database_software['version'] .' <span class="ok">[OK]</span><br>'
					. '<span class="warning">'. $database_software['name'] .' '. $database_software['recommended_version'] .'+ recommended</span></span></p>';

			} else {
				echo $database_software['name'] .' '. $database_software['version'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
			}

			### Backup > Database ##################################################

			if (isset($_REQUEST['backup']) && !preg_match('#^(0|false|no|off)$#i', $_REQUEST['backup'])) {

				echo '<p>Backing up the database... ';

				if (!file_exists(FS_DIR_STORAGE . 'backups/')) {
					if (!mkdir(FS_DIR_STORAGE . 'backups/', 0777)) {
						throw new Exception('Could not create folder for backups');
					}
				}

				$platform_database_version = database::query(
					"select `value` from ". DB_TABLE_PREFIX ."settings
					where `key` = 'platform_database_version'
					limit 1;"
				)->fetch('value');

				$backup_file = FS_DIR_STORAGE . 'backups/'. PLATFORM_NAME .'-'. date('Ymd-Hi') .'-database-'. $platform_database_version .'.sql';

				if (!$backup_handle = fopen($backup_file, 'wb')) {
					throw new Exception("Cannot open backup file for writing ($backup_file)");
				}

				if (!flock($backup_handle, LOCK_EX)) {
					throw new Exception("Could not aquire an exlusive lock for writing to file ($backup_file)");
				}

				$separator = '-- -----';

				$tables_query = database::query('SHOW TABLES');
				while ($table = database::fetch($tables_query)) {
					$table = array_shift($table);

					if (!preg_match('#^'. preg_quote(DB_TABLE_PREFIX, '#') .'#', $table)) continue;

					if (!empty($use_initial_separator)) {
						$output .= $separator . PHP_EOL;
						$use_initial_separator = true;
					}

					// Drop Table
					$output = "DROP TABLE IF EXISTS `" . $table . "`;" . PHP_EOL;

					// Create Table
					$query = database::query("SHOW CREATE TABLE `" . $table . "`;");
					if ($row = database::fetch($query)) {
						$output = $separator . PHP_EOL
										. $row['Create Table'] . ';' . PHP_EOL;
					}

					fwrite($backup_handle, $output);

					if (!empty($ignore_tables) && in_array($table, $ignore_tables)) continue;

					// Insert Data
					$columns = database::query(
						"SHOW COLUMNS FROM `" . $table ."`"
					)->fetch_all('Field');

					$rows_query = database::query(
						"SELECT `" . implode('`, `', $columns) . "` FROM `" . $table ."`"
					);

					if (!database::num_rows($rows_query)) continue;

					$output = $separator . PHP_EOL
									. "INSERT INTO `" . $table . "` (`" . implode("`, `", $columns) . "`) VALUES " . PHP_EOL;

					while ($row = database::fetch($rows_query)) {

						foreach ($columns as $column) {
							if (!isset($row[$column])) {
								$row[$column] = "NULL, ";
							} elseif ($row[$column] != '') {
								$row[$column] = "'". addcslashes($row[$column], "\\'\r\n") ."'";
							} else {
								$row[$column] = "'', ";
							}
						}

						$output .= "(". implode(", ", $row) . ")," . PHP_EOL;
					}

					$output = rtrim($output, ", ") . ";" . PHP_EOL;

					fwrite($backup_handle, $output);
				}

				flock($backup_handle, LOCK_UN);
				fclose($backup_handle);

				echo '<span class="ok">[OK]</span> '. $backup_file .'</p>' . PHP_EOL . PHP_EOL;
			}

			### App > Check Version ################################################

			echo '<p>Checking application database version... ';

			if (defined('PLATFORM_DATABASE_VERSION')) {
				echo PLATFORM_DATABASE_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

			} else if (!empty($_REQUEST['from_version'])) {
				define('PLATFORM_DATABASE_VERSION', $_REQUEST['from_version']);
				echo $_REQUEST['from_version'] . ' (User Defined) <span class="warning">[OK]</span></p>' . PHP_EOL . PHP_EOL;

			} else {
				throw new Exception(' <span class="error">[Undetected]</span></p>' . PHP_EOL . PHP_EOL);
			}

			### Installer > Update #################################################

			if (!empty($_REQUEST['skip_updates'])) {

				echo '<p>Checking for updates... ';

				require_once FS_DIR_APP . 'includes/clients/http_client.inc.php';
				$client = new http_client();

				$update_file = function($file) use ($client) {

					$response = $client->call('GET', 'https://raw.githubusercontent.com/litecart/litecart/'. PLATFORM_VERSION .'/public_html/'. $file);

					if ($client->last_response['status_code'] != 200) return false;

					if (!is_dir(dirname(FS_DIR_APP . $file))) {
						mkdir(dirname(FS_DIR_APP . $file), 0777, true);
					}

					file_put_contents(FS_DIR_APP . $file, $response);

					return true;
				};

				$calculate_md5 = function($file) {
					if (!is_file(FS_DIR_APP . $file)) return;
					$contents = preg_replace('#(\r\n?|\n)#', "\n", file_get_contents(FS_DIR_APP . $file));
					return md5($contents);
				};

				if ($update_file('install/checksums.md5')) {

					$files_updated = 0;
					foreach (file(FS_DIR_APP . 'install/checksums.md5', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
						list($checksum, $file) = explode("\t", $line);
						if ($calculate_md5($file) != $checksum) {
							if ($update_file($file)) $files_updated++;
						}
					}

					if (!empty($files_updated)) {
						echo 'Updated '. $files_updated .' file(s) <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
					}
				}
			}

			########################################################################

			$current_version = PLATFORM_DATABASE_VERSION;

			foreach ($supported_versions as $version) {

				if (version_compare(PLATFORM_DATABASE_VERSION, $version, '>=')) {
					continue;
				}

				if (version_compare($current_version, '3.0.0', '>=')) {
					database::query('start transaction;');
				}

				if (file_exists(__DIR__ . '/migrations/'. $version .'.sql')) {

					echo '<p>Upgrading database to '. $version .'...</p>' . PHP_EOL . PHP_EOL;
					$sql = file_get_contents(__DIR__ . '/migrations/'. $version .'.sql');
					$sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

					foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
						$query = preg_replace('#^-- .*?\R+#m', '', $query);
						if (!empty($query)) {
							database::query($query);
						}
					}
				}

				if (file_exists(__DIR__ . '/migrations/'. $version .'.inc.php')) {
					echo '<p>Upgrading system to '. $version .'...</p>' . PHP_EOL . PHP_EOL;
					include(__DIR__ . '/migrations/'. $version .'.inc.php');
				}

				if (version_compare($current_version, '3.0.0', '>=')) {
					database::query('commit;');
				}

				echo '<p>Set platform database version...';

				database::query(
					"update ". DB_TABLE_PREFIX ."settings
					set `value` = '". database::input($version) ."'
					where `key` = 'platform_database_version'
					limit 1;"
				);

				echo ' <strong>'. $version .'</strong></p>' . PHP_EOL . PHP_EOL;

				$current_version = $version;
			}

			########################################################################

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
			$database_structure = json_decode(file_get_contents(__DIR__ . '/structure.json'), true);

			if ($database_structure === null) {
				throw new Exception('structure.json could not be decoded: ' . json_last_error_msg());
			}

			if (empty($database_structure['tables'])) {
				throw new Exception('structure.json does not contain any tables.');
			}

			// Replace table prefix in structure.json
			foreach ($database_structure['tables'] as $table_name => $table) {
				$platform_table_name = preg_replace('#^lc_#', DB_TABLE_PREFIX, $table_name);
				$database_structure['tables'][$platform_table_name] = $table;
				unset($database_structure['tables'][$table_name]);
			}

			#############################################

			// Iterate through each table (this is to ensure specific table properties)
			foreach ($database_structure['tables'] as $table_name => $table) {

				// If table exists
				if (in_array($table_name, $existing_tables)) {

					// Get existing table properties
					$existing_table = database::query(
						"SHOW TABLE STATUS LIKE '". database::input($table_name) ."';"
					)->fetch();

					// Convert engine and row format if needed
					if ($existing_table['Engine'] != 'InnoDB' || $existing_table['Row_format'] != 'Dynamic') {
						database::query(
							"ALTER TABLE `". $table_name ."`
							ENGINE='InnoDB' ROW_FORMAT=DYNAMIC;"
						);
					}

					// Convert charset and collation if needed
					if ($existing_table['Create_options'] != 'CHARSET=utf8mb4' || $existing_table['Collation'] != $default_collation) {
						database::query(
							"ALTER TABLE `". $table_name ."`
							CONVERT TO CHARACTER SET utf8mb4 COLLATE ". database::input($default_collation) .";"
						);
					}
				}
			}

			########################################################################

			// Iterate through each table and add/change columns and keys
			foreach ($database_structure['tables'] as $table_name => $table) {

				if (empty($table['columns'])) {
					throw new Exception('Table structure for '. $table_name .' in structure.json does not contain any columns');
				}

				if (in_array($table_name, $existing_tables)) {
					$table_exists = true;
				} else {
					$table_exists = false;
				}

				if ($table_exists) {
					$sql = 'ALTER TABLE `'. $table_name .'`' . PHP_EOL;
				} else {
					$sql = 'CREATE TABLE `'. $table_name .'` (' . PHP_EOL;
				}

				$last_column = null;

				// Drop primary key
				if ($table_exists && !empty($table['primary_key'])) {
					if (database::query(
						"SHOW INDEX FROM `". $table_name ."`
						WHERE Key_name = 'PRIMARY'
						/*AND non_unique = 0*/;"
					)->num_rows) {
						$sql .= 'DROP PRIMARY KEY,' . PHP_EOL;
					}
				}

				// Drop unique keys
				if ($table_exists && !empty($table['unique_keys'])) {
					foreach (array_keys($table['unique_keys']) as $key_name) {
						if (database::query(
							"SHOW INDEX FROM `". $table_name ."`
							WHERE Key_name = '". database::input($key_name) ."'
							AND non_unique = 0;"
						)->num_rows) {
							$sql .= 'DROP INDEX `'. $key_name .'`,' . PHP_EOL;
						}
					}
				}

				// Drop keys
				if ($table_exists && !empty($table['keys'])) {
					foreach (array_keys($table['keys']) as $key_name) {
						if (database::query(
							"SHOW INDEX FROM `". $table_name ."`
							WHERE Key_name = '". database::input($key_name) ."'
							AND non_unique = 1;"
						)->num_rows) {
							$sql .= 'DROP INDEX `'. $key_name .'`,' . PHP_EOL;
						}
					}
				}

				// Drop check constraints
				if ($table_exists && !empty($table['check_constraints'])) {
					foreach (array_keys($table['check_constraints']) as $name) {
						if (database::query(
							"SELECT CONSTRAINT_NAME
							FROM information_schema.table_constraints
							WHERE CONSTRAINT_SCHEMA = '". DB_DATABASE ."'
							AND TABLE_NAME = '". database::input($table_name) ."'
							AND CONSTRAINT_NAME = '". database::input($name) ."';"
						)->num_rows) {
							$sql .= 'DROP CHECK `'. $name .'`,' . PHP_EOL;
						}
					}
				}

				// Add/change columns
				foreach ($table['columns'] as $column_name => $column) {

					if ($table_exists && database::query(
						"SHOW COLUMNS FROM `". $table_name ."`
						LIKE '". database::input($column_name) ."';"
					)->num_rows) {
						$column_exists = true;
					} else {
						$column_exists = false;
					}

					if ($table_exists) {

						if ($column_exists) {
							$sql .= 'CHANGE COLUMN `'. $column_name .'` `'. $column_name .'` '. $column['type'];
						} else {
							$sql .= 'ADD COLUMN `'. $column_name .'` '. $column['type'];
						}

					} else {
						$sql .= '  `'. $column_name .'` '. $column['type'];
					}

					if (isset($column['length'])) {
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
						$sql .= ' DEFAULT '. $column['default'] .'';
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
						$sql .= '  FULLTEXT KEY `' . database::input($key_name) . '` (`' . implode('`, `', database::input($key_columns)) . '`),' . PHP_EOL;
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
							$sql .= 'ADD CONSTRAINT `'. database::input($name) .'` CHECK ('. database::input($expression) .'),' . PHP_EOL;
						} else {
							$sql .= '  CONSTRAINT `'. database::input($name) .'` CHECK ('. database::input($expression) .'),' . PHP_EOL;
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
			}

			########################################################################

			if (!is_dir(__DIR__.'/../../.git')) {

				echo '<p>Preparing CSS files...</p>' . PHP_EOL . PHP_EOL;

				perform_action('delete', [
					FS_DIR_APP . 'backend/template/less/',
				]);

				if (!empty($_REQUEST['development_type']) && $_REQUEST['development_type'] == 'advanced') {

					file_put_contents(FS_DIR_APP . 'includes/templates/default.catalog/.development', 'advanced');

					perform_action('delete', [
						FS_DIR_APP . 'frontend/templates/*/css/app.css',
						FS_DIR_APP . 'frontend/templates/*/css/checkout.css',
						FS_DIR_APP . 'frontend/templates/*/css/framework.css',
						FS_DIR_APP . 'frontend/templates/*/css/printable.css',
						FS_DIR_APP . 'frontend/templates/*/js/app.js',
					]);

				} else {

					file_put_contents(FS_DIR_APP . 'includes/templates/default.catalog/.development', 'standard');

					perform_action('delete', [
						FS_DIR_APP . 'frontend/templates/*/css/*.min.css',
						FS_DIR_APP . 'frontend/templates/*/css/*.min.css.map',
						FS_DIR_APP . 'frontend/templates/*/less/',
						FS_DIR_APP . 'frontened/templates/default.catalog/js/*.min.js.map',
						FS_DIR_APP . 'frontend/templates/default.catalog/less/',
					]);

					perform_action('modify', [
						FS_DIR_APP . 'frontend/templates/*/layouts/*.inc.php' => [
							['search' => 'app.min.css',       'replace' => 'app.css'],
							['search' => 'checkout.min.css',  'replace' => 'checkout.css'],
							['search' => 'framework.min.css', 'replace' => 'framework.css'],
							['search' => 'printable.min.css', 'replace' => 'printable.css'],
							['search' => 'app.min.js',        'replace' => 'app.js'],
						],
					]);
				}

				echo PHP_EOL;
			}

			########################################################################

			echo '<p>Reset error log... ';

			if (file_put_contents(FS_DIR_STORAGE . 'logs/errors.log', '') !== false) {
				echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
			} else {
				echo ' <span class="error">[Failed]</span></p>' . PHP_EOL . PHP_EOL;
			}

			########################################################################

			echo '<p>Clear cache... ';

			database::query(
				"update ". DB_TABLE_PREFIX ."settings
				set value = '1'
				where `key` = 'cache_clear'
				limit 1;"
			);

			perform_action('delete', [
				FS_DIR_STORAGE . 'vmods/.cache/*.php',
				FS_DIR_STORAGE . 'vmods/.cache/.checked',
				FS_DIR_STORAGE . 'vmods/.cache/.modifications',
			]);

			echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

			########################################################################

			echo '<h2>Complete</h2>' . PHP_EOL . PHP_EOL
				 . '<p style="font-weight: bold;">Upgrade complete! Please delete the <strong>~/install/</strong> folder.</p>' . PHP_EOL . PHP_EOL;

			if (!empty($_REQUEST['redirect'])) {
				header('Location: '. $_REQUEST['redirect']);
				exit;
			}

		} catch (Exception $e) {

			// Rollback if we are in a transaction
			if (defined('PLATFORM_DATABASE_VERSION') && version_compare(PLATFORM_DATABASE_VERSION, '3.0.0', '>=')) {
				database::query('rollback;');
			}

			echo implode(PHP_EOL, [
				'<h2>Upgrade Failed</h2>',
				'',
				'<p style="font-weight: bold;">The upgrade failed. Please check the error log for more information.</p>',
				'',
				'<p>Error: '. htmlspecialchars($e->getMessage()) .'</p>',
				'',
			]);
		}

		echo ob_get_clean();

		if ($_SERVER['SERVER_SOFTWARE'] == 'CLI') exit;

		require('includes/footer.inc.php');
		exit;
	}

?>
<style>
html {
	display: table;
	width: 100%;
}
body {
	display: table-cell;
	vertical-align: middle;
}
.glass-edges {
	max-width: 640px;
}
input[name="development_type"] {
	display: none;
}
input[name="development_type"] + div {
	display: inline-block;
	padding: 15px;
	margin: 7.5px;
	border: 1px solid rgba(0, 0, 0, .1);
	border-radius: 15px;
	width: 250px;
	height: 145px;
	text-align: center;
	cursor: pointer;
}
input[name="development_type"] + div .type {
	font-size: 1.5em;
	line-height: 1.5em;
}
input[name="development_type"] + div .title {
	font-size: 1.25em;
	font-weight: bold;
	line-height: 1.5em;
}
input[name="development_type"]:checked + div {
	border-color: #333;
}
</style>

<form name="upgrade_form" method="post">
	<h1>Upgrade <?php echo PLATFORM_VERSION; ?></h1>

	<h2>Application</h2>

	<div class="grid">
		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">MySQL/MariaDB Server</div>
				<div class="form-input">
					<?php echo DB_SERVER; ?>
				</div>
			</label>
		</div>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">MySQL/MariaDB Database</div>
				<div class="form-input">
					<?php echo DB_DATABASE; ?>
				</div>
			</label>
		</div>
	</div>

	<div class="grid">
		<?php if (defined('PLATFORM_DATABASE_VERSION')) { ?>
		<div class="col-md-3">
			<label class="form-group">
				<div class="form-label">Current Version</div>
				<div class="form-input"><?php echo PLATFORM_DATABASE_VERSION; ?></div>
			</label>
		</div>
		<?php } else { ?>
		<div class="col-md-3">
			<label class="form-group">
				<div class="form-label">Select the <?php echo PLATFORM_NAME; ?> version you are upgrading from:</div>
				<select class="form-input" name="from_version">
					<option value="">-- Select Version --</option>
					<?php foreach ($supported_versions as $version) echo '<option value="'. $version .'"'. ((isset($_REQUEST['from_version']) && $_REQUEST['from_version'] == $version) ? 'selected' : '') .'>'. PLATFORM_NAME .' '. $version .'</option>' . PHP_EOL; ?>
				</select>
			</label>
		</div>
		<?php } ?>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">New Version</div>
				<div class="form-input"><?php echo PLATFORM_VERSION; ?></div>
			</label>
		</div>
	</div>

	<label class="form-group">
		<input class="form-check" type="checkbox" name="backup" value="true" checked> Backup my database before performing the upgrade.
	</label>

	<label class="form-group">
		<input type="checkbox" class="form-check" name="skip_updates" value="0"> Skip downloading the latest updates
	</label>

	<h2>Development</h2>

	<div class="form-group" style="display: flex;">
		<label>
			<input name="development_type" value="standard" type="radio" checked>
			<div>
				<div class="type">Standard</div>
				<div class="title">
					.css<br>
					.js
				</div>
				<small class="description">(Uncompressed files)</small>
			</div>
		</label>

		<label>
			<input name="development_type" value="advanced" type="radio">
			<div>
				<div class="type">Advanced</div>
				<div class="title">
					.less + .min.css<br>
					.js + .min.js
				</div>
				<small class="description">
					(Requires a <a href="https://www.litecart.net/addons/163/developer-kit" target="_blank">LESS compiler</a>)
				</small>
			</div>
		</label>
	</div>

	<button class="btn btn-success btn-block" type="submit" name="upgrade" value="true" onclick="if(!confirm('Warning! The procedure cannot be undone.')) return false;" style="font-size: 1.5em; padding: 0.5em;">Upgrade To <?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?></button>
</form>

<?php	require 'includes/footer.inc.php'; ?>