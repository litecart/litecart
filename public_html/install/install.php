<?php

	ini_set('display_errors', 'On');
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');

	include __DIR__ . '/../includes/compatibility.inc.php';

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
				'  --db_server          Set database hostname (Default: localhost)',
				'  --db_username        Set database username',
				'  --db_password        Set database user password',
				'',
				'  --db_database        Set database name',
				'  --db_table_prefix    Set database table prefix (Default: lc_).',
				'  --db_collation       Set database collation (Default: utf8mb4_swedish_ci)',
				'  --document_root      Set document root',
				'',
				'  --timezone           Set timezone e.g. Europe/London',
				'',
				'  --admin_folder       Set admin folder name (Default: admin)',
				'  --username           Set admin username',
				'  --password           Set admin user password',
				'',
				'  --development_type   Set development type "standard" or "advanced" (Default: standard)',
				'  --cleanup            Delete the install/ directory after finishing the installation.',
				'',
			]);
			exit;
		}

		$_REQUEST = getopt('', [
			'db_server::', 'db_username:', 'db_password::', 'db_database:', 'db_table_prefix::', 'db_collation::',
			'document_root:', 'timezone::', 'admin_folder::', 'username::', 'password::', 'development_type:: cleanup::',
		]);

		$_REQUEST['install'] = true;
	}

	if (empty($_REQUEST['install'])) {
		header('Location: index.php');
		exit;
	}

	ob_start(function($buffer) {
		if ($_SERVER['SERVER_SOFTWARE'] == 'cli') {
			$buffer = strip_tags($buffer);
			exit;
		}
		return $buffer;
	});

	define('VMOD_DISABLED', 'true');

	require __DIR__ . '/includes/header.inc.php';
	require __DIR__ . '/includes/functions.inc.php';

	$requirements = json_decode(file_get_contents(__DIR__ . '/requirements.json'), true);

	try {

		register_shutdown_function(function(){
			$buffer = ob_get_clean();
			echo ($_SERVER['SERVER_SOFTWARE'] == 'CLI') ? strip_tags($buffer) : $buffer;
		});

		echo '<h1>LiteCart Installer</h1>' . PHP_EOL . PHP_EOL;

		### Parameters > Check ########################################

		echo '<p>Checking installation parameters...';

		if (!empty($_SERVER['DOCUMENT_ROOT'])) {
			define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/') . '/');
		} else if ($_SERVER['SERVER_SOFTWARE'] == 'CLI' && !empty($_REQUEST['document_root'])) {
			define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', realpath($_REQUEST['document_root'])), '/') . '/');
		} else {
			throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . ' Could not detect \$_SERVER[\'DOCUMENT_ROOT\']. If you are using CLI, make sure you pass the parameter "document_root" e.g. --document_root="/var/www/mysite.com/public_html"</p>' . PHP_EOL  . PHP_EOL);
		}

		define('FS_DIR_APP',     rtrim(str_replace('\\', '/', realpath(__DIR__.'/../')), '/') . '/');
		define('FS_DIR_STORAGE', FS_DIR_APP .'/storage/');

		define('WS_DIR_APP',     preg_replace('#^'. preg_quote(rtrim(DOCUMENT_ROOT, '/'), '#') .'#', '', FS_DIR_APP));
		define('WS_DIR_STORAGE', WS_DIR_APP .'storage/');

		require __DIR__ . '/../includes/app_header.inc.php';

		if (!defined('PLATFORM_NAME')) {
			throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'Could not get platform name</p>' . PHP_EOL  . PHP_EOL);
		}

		if (!defined('PLATFORM_VERSION')) {
			throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'Could not get platform version</p>' . PHP_EOL  . PHP_EOL);
		}

		if (!empty($_REQUEST['admin_folder'])) {
			$_REQUEST['admin_folder'] = basename(trim(str_replace('\\', '/', $_REQUEST['admin_folder']), '/'));
		} else {
			$_REQUEST['admin_folder'] = 'admin';
		}

		if (empty($_REQUEST['db_server'])) {
			$_REQUEST['db_server'] = '127.0.0.1';
		}

		if (empty($_REQUEST['db_username'])) {
			throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'No MySQL/MariaDB user provided</p>' . PHP_EOL  . PHP_EOL);
		}

		if (empty($_REQUEST['db_password'])) {
			$_REQUEST['db_password'] = '';
		}

		if (empty($_REQUEST['db_database'])) {
			throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'No MySQL/MariaDB database provided</p>' . PHP_EOL  . PHP_EOL);
		}

		if (empty($_REQUEST['db_collation'])) {
			$_REQUEST['db_collation'] = 'utf8mb4_swedish_ci';
		}

		if (!isset($_REQUEST['db_table_prefix'])) {
			$_REQUEST['db_table_prefix'] = 'lc_';
		}

		if (!isset($_REQUEST['username'])) {
			$_REQUEST['username'] = 'admin';
		}

		if (!isset($_REQUEST['password'])) {
			$_REQUEST['password'] = '';
		}

		// Start off with a clean slate
		ini_set('error_log', FS_DIR_STORAGE . 'logs/errors.log');

		if (empty($_REQUEST['timezone']) && !empty($_REQUEST['store_time_zone'])) {
			$_REQUEST['timezone'] = $_REQUEST['store_time_zone']; // Backwards compatible

		} else if (empty($_REQUEST['timezone']) && ini_get('date.timezone')) {
			$_REQUEST['timezone'] = ini_get('date.timezone');

		} else if (empty($_REQUEST['timezone'])) {
			throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'No time zone provided</p>' . PHP_EOL  . PHP_EOL);
		}

		define('BACKEND_ALIAS', $_REQUEST['admin_folder']);
		define('DB_SERVER', $_REQUEST['db_server']);
		define('DB_USERNAME', $_REQUEST['db_username']);
		define('DB_PASSWORD', $_REQUEST['db_password']);
		define('DB_DATABASE', $_REQUEST['db_database']);
		define('DB_TABLE_PREFIX', $_REQUEST['db_table_prefix']);

		echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		### Environment > Set #########################################

		error_reporting(E_ALL);
		ini_set('ignore_repeated_errors', 'Off');
		ini_set('log_errors', 'On');
		ini_set('display_errors', 'On');
		ini_set('html_errors', 'On');
		ini_set('error_log', FS_DIR_STORAGE . 'logs/errors.log');
		date_default_timezone_set(!empty($_REQUEST['timezone']) ? $_REQUEST['timezone'] : ini_get('date.timezone'));

		### PHP > Check Version #######################################

		echo '<p>Checking PHP version... ';

		if (version_compare(PHP_VERSION, '5.6', '<')) {
			throw new Exception(PHP_VERSION .' <span class="error">[Error] PHP 5.6+ minimum requirement</span></p>' . PHP_EOL . PHP_EOL);

		} else if (version_compare(PHP_VERSION, '7.2', '<=')) {
			echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' has reached <a href="https://www.php.net/supported-versions.php" target="_blank">end of life</a>.</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			echo PHP_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		}

		### PHP > Check PHP Extensisons ###############################

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

		### PHP > Check Disabled Functions ############################

		echo '<p>Checking available PHP functions... ';

		$critical_functions = ['error_log', 'ini_set'];
		$important_functions = ['allow_url_fopen', 'shell_exec', 'exec', 'apache_get_modules'];

		if ($disabled_functions = array_intersect($critical_functions, preg_split('#\s*,\s*#', ini_get('disable_functions'), -1, PREG_SPLIT_NO_EMPTY))) {
			throw new Exception('<span class="error">[Error] Critical functions are disabled ('. implode(', ', $disabled_functions) .'). You need to unblock them in php.ini</span></p>' . PHP_EOL . PHP_EOL);

		} else if ($disabled_functions = array_intersect($important_functions, preg_split('#\s*,\s*#', ini_get('disable_functions'), -1, PREG_SPLIT_NO_EMPTY))) {
			echo '<span class="warning">[Warning] Some common functions are disabled ('. implode(', ', $disabled_functions) .'). It is recommended that you unblock them in php.ini.</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		}

		### PHP > Check display_errors ################################

		echo '<p>Checking PHP display_errors... ';

		if (in_array(strtolower(ini_get('display_errors')), ['1', 'true', 'on', 'yes'])) {
			echo ini_get('display_errors') . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			echo ini_get('display_errors') . ' <span class="warning">[Warning] Missing permissions to display errors?</span></p>' . PHP_EOL . PHP_EOL;
		}

		### PHP > Check document root #################################

		if ($_SERVER['SERVER_SOFTWARE'] != 'CLI') {
			echo '<p>Checking $_SERVER["DOCUMENT_ROOT"]... ';

			if (DOCUMENT_ROOT . preg_replace('#/index\.php$#', '', strtok($_SERVER['REQUEST_URI'], '?')) != str_replace('\\', '/', __DIR__)) {
				echo $_SERVER['DOCUMENT_ROOT'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

			} else {
				echo $_SERVER['DOCUMENT_ROOT'] . ' <span class="warning">[Warning]</span> There is a problem with your web server configuration causing $_SERVER["DOCUMENT_ROOT"] and __DIR__ to return conflicting paths. Contact your web host and have them correcting this.</p>' . PHP_EOL  . PHP_EOL;
			}
		}

		### Storage ###################################################

		echo '<p>Set up storage folder... ';

		if (file_exists(FS_DIR_STORAGE) || mkdir(FS_DIR_STORAGE, 0777)) {
			echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			throw new Exception('<span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL);
		}

		### Logs ###################################################

		echo '<p>Set up logs folder... ';

		if (file_exists(FS_DIR_STORAGE . 'logs/') || mkdir(FS_DIR_STORAGE . 'logs/', 0777)) {
			echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			throw new Exception('<span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL);
		}

		### Installer > Update ########################################

		echo '<p>Checking for updates... ';

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
		} else {
			echo ' <span class="warning">[Skipped]</span></p>' . PHP_EOL . PHP_EOL;
		}

		### Database > Connection #####################################

		echo '<p>Connecting to database server on '. DB_SERVER .'... ';

		if (!extension_loaded('mysqli')) {
			throw new Exception(' <span class="error">[Error]</span> MySQLi is not installed or configured for PHP</p>' . PHP_EOL  . PHP_EOL);

		} else if (!database::connect('default', DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE, 'utf8')) {
			throw new Exception(' <span class="error">[Error]</span> Unable to connect</p>' . PHP_EOL  . PHP_EOL);

		} else {
			echo 'Connected! <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		}

		### Database > Check Version ##################################

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

		### Database > Check Charset ##################################

		echo '<p>Checking database default character set... ';

		$charset = database::query(
			"select DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME from information_schema.SCHEMATA
			where schema_name = '". database::input(DB_DATABASE) ."'
			limit 1;"
		)->fetch();

		if (!$charset) {
			throw new Exception(' <span class="error">[Error] Failed to retrieve character set</span></p>');
		}

		if (strtok($charset['DEFAULT_CHARACTER_SET_NAME'], '_') != strtok($_REQUEST['db_collation'], '_')) {

			if (!empty($_REQUEST['set_default_collation'])) {

				database::query(
					"ALTER DATABASE `". DB_DATABASE ."`
					CHARACTER SET ". strtok($_REQUEST['db_collation'], '_') ." COLLATE ". $_REQUEST['db_collation'] .";"
				);

				echo 'Setting '. strtok($_REQUEST['db_collation'], '_') . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

			} else {
				echo $charset['DEFAULT_CHARACTER_SET_NAME'] . ' <span class="warning">[Warning]</span> The database default charset is not \''. strtok($_REQUEST['db_collation'], '_') .'\' and you might experience problems with mixed character sets in the future. Try performing the following MySQL/MariaDB query: "ALTER DATABASE `'. DB_DATABASE .'` CHARACTER SET '. strtok($_REQUEST['db_collation'], '_') .' COLLATE '. $_REQUEST['db_collation'] .';"</p>';
			}

		} else {
			echo $charset['DEFAULT_CHARACTER_SET_NAME'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		}

		echo '<p>Checking database default collation... ';

		if ($charset['DEFAULT_COLLATION_NAME'] != $_REQUEST['db_collation']) {

			if (!empty($_REQUEST['set_default_collation'])) {

				database::query(
					"ALTER DATABASE `". DB_DATABASE ."`
					CHARACTER SET ". strtok($_REQUEST['db_collation'], '_') ." COLLATE ". $_REQUEST['db_collation'] .";"
				);

				echo 'Setting '. $_REQUEST['db_collation'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

			} else {
				echo $charset['DEFAULT_COLLATION_NAME'] . ' <span class="warning">[Warning]</span> The database default collation is not \''. $_REQUEST['db_collation'] .'\' and you might experience future trouble with mixed collations. Try performing the following MySQL query: "ALTER DATABASE `'. DB_DATABASE .'` CHARACTER SET '. strtok($_REQUEST['db_collation'], '_') .' COLLATE '. $_REQUEST['db_collation'] .';"</p>';
			}

		} else {
			echo $charset['DEFAULT_COLLATION_NAME'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		}

		### Config > Write ############################################

		echo '<p>Writing config file... ';

		$config = strtr(file_get_contents('config'), [
			'{STORAGE_FOLDER}' => 'storage',
			'{ADMIN_FOLDER}' => BACKEND_ALIAS,
			'{DB_SERVER}' => DB_SERVER,
			'{DB_USERNAME}' => DB_USERNAME,
			'{DB_PASSWORD}' => DB_PASSWORD,
			'{DB_DATABASE}' => DB_DATABASE,
			'{DB_TABLE_PREFIX}' => DB_TABLE_PREFIX,
			'{CLIENT_IP}' => $_REQUEST['client_ip'],
			'{TIMEZONE}' => $_REQUEST['timezone'],
		]);

		if (file_put_contents(FS_DIR_STORAGE . 'config.inc.php', $config) !== false) {
			echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			throw new Exception('<span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL);
		}

		### Database > Cleaning #######################################

		echo '<p>Cleaning database... ';

		// Decode database structure defined in structure.json
		$database_structure = json_decode(file_get_contents('structure.json'), true);

		// Check if structure.json could be decoded
		if ($database_structure === null) {
			throw new Exception('structure.json could not be decoded: ' . json_last_error_msg());
		}

		// Check if structure.json contains any tables
		if (empty($database_structure['tables'])) {
			throw new Exception('structure.json does not contain any tables.');
		}

		// Iterate through tables and drop them
		foreach (array_keys($database_structure['tables']) as $table_name) {

			$table_name = preg_replace('#^lc_#', DB_TABLE_PREFIX, $table_name);

			database::query(
				"DROP TABLE IF EXISTS `". database::input($table_name) ."`;"
			);
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		### Database > Tables > Structure #############################

		echo '<p>Writing database tables... ';

		// Iterate through tables
		foreach ($database_structure['tables'] as $table_name => $table) {

			// Check if table contains any columns
			if (empty($table['columns'])) {
				throw new Exception('Table ' . $table_name . ' does not contain any columns.');
			}

			$table_name = preg_replace('#^lc_#', DB_TABLE_PREFIX, $table_name);

			// Create SQL statement
			$sql = 'CREATE TABLE `' . database::input($table_name) . '` (' . PHP_EOL;

			foreach ($table['columns'] as $column_name => $column) {

				$sql .= '  `' . $column_name . '` ' . $column['type'];

				if (isset($column['length'])) {
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
					$sql .= ' DEFAULT '. $column['default'] .'';
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
					$sql .= 'CONSTRAINT `'. database::input($name) .'` CHECK ('. database::input($expression) .'),' . PHP_EOL;
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
				str_replace('`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,', '`updated_at` TIMESTAMP NOT NULL DEFAULT NOW(),', $sql);
			}

			// Execute SQL statement
			database::query($sql);
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		### Database > Tables > Data ##################################

		echo '<p>Writing database table data... ';

		$sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, file_get_contents('data.sql'));

		foreach ([
			'{STORE_NAME}' => isset($_REQUEST['store_name']) ? $_REQUEST['store_name'] : '',
			'{STORE_EMAIL}' => isset($_REQUEST['store_email']) ? $_REQUEST['store_email'] : '',
			'{STORE_TIME_ZONE}' => isset($_REQUEST['store_time_zone']) ? $_REQUEST['store_time_zone'] : '',
			'{STORE_COUNTRY_CODE}' => isset($_REQUEST['country_code']) ? $_REQUEST['country_code'] : '',
		] as $search => $replace) {
			$sql = str_replace($search, database::input($replace), $sql);
		}

		foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
			$query = preg_replace('#^-- .*?\R+#m', '', $query);
			database::query($query);
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		### Files > Default Data ######################################

		echo '<p>Copying default files...</p>' . PHP_EOL;

		perform_action('copy', [
			'data/default/public_html/' => FS_DIR_APP,
			'data/default/storage/' => FS_DIR_STORAGE,
		]);

		echo PHP_EOL;

		### .htaccess mod rewrite #####################################

		echo '<p>Setting mod_rewrite base path...';

		$htaccess = file_get_contents('htaccess');

		$htaccess = strtr($htaccess, [
			'{WS_DIR_APP}' => WS_DIR_APP,
			'{FS_DIR_APP}' => FS_DIR_APP,
		]);

		if (file_put_contents('../.htaccess', $htaccess)) {
			echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		} else {
			echo ' <span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL;
		}

		### Admin > Database > Administrators ##################################

		database::query(
			"insert into ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_administrators`') ."
			(`id`, `status`, `username`, `password_hash`, `known_ips`, `updated_at`, `created_at`)
			values ('1', '1', '". database::input($_REQUEST['username']) ."', '". database::input(password_hash($_REQUEST['password'], PASSWORD_DEFAULT)) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
		);

		### Set platform database version #############################

		echo '<p>Set platform database version...';

		if (defined('PLATFORM_VERSION')) {

			database::query(
				"update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_settings`') ."
				set `value` = '". database::input(PLATFORM_VERSION) ."'
				where `key` = 'platform_database_version'
				limit 1;"
			);

			echo ' <strong>'. PLATFORM_VERSION .'</strong></p>' . PHP_EOL . PHP_EOL;

		} else {
			echo ' <span class="error">[Error: Not defined]</span></p>' . PHP_EOL . PHP_EOL;
		}

		### Regional Data Patch #######################################

		if (!empty($_REQUEST['country_code'])) {
			echo '<p>Patching installation with regional data...' . PHP_EOL;

			$directories = functions::file_search('data/*{'. $_REQUEST['country_code'] .',XX}*/', GLOB_BRACE);

			if (!empty($directories)) {
				foreach ($directories as $dir) {

					$dir = basename($dir);
					if ($dir == 'demo') continue;
					if ($dir == 'default') continue;

					foreach (glob('data/'. $dir .'/*.sql') as $file) {
						$sql = file_get_contents($file);

						if (empty($sql)) continue;

						$sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

						foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
							$query = preg_replace('#^-- .*?\R+#m', '', $query);
							database::query($query);
						}
					}
				}

				perform_action('copy', [
					"data/$dir/public_html/" => FS_DIR_APP,
					"data/$dir/storage/" => FS_DIR_STORAGE,
				]);
			}

			echo PHP_EOL;
		}

		### Database > Tables > Demo Data #############################

		if (!empty($_REQUEST['demo_data'])) {
			echo '<p>Writing demo data... ';

			$sql = file_get_contents('data/demo/data.sql');

			if (!empty($sql)) {
				$sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

				foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
					$query = preg_replace('#^-- .*?\R+#m', '', $query);
					database::query($query);
				}
			}

			echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		}

		### Files > Demo Data #########################################

		if (!empty($_REQUEST['demo_data'])) {
			echo '<p>Copying demo files...</p>' . PHP_EOL;

			perform_action('copy', ['data/demo/storage/' => FS_DIR_STORAGE]);

			echo PHP_EOL;
		}

		### Files > Development Type ##################################

		if (!is_dir(__DIR__.'/../../.git')) {

			echo '<p>Preparing CSS files...</p>' . PHP_EOL;

			perform_action('delete', [
				FS_DIR_APP . 'backend/template/less/',
			]);

			if (!empty($_REQUEST['development_type']) && $_REQUEST['development_type'] == 'advanced') {

				file_put_contents(FS_DIR_APP . 'frontend/templates/default/.development', 'advanced');

				perform_action('delete', [

					FS_DIR_APP . 'frontend/templates/*/css/app.css',
					FS_DIR_APP . 'frontend/templates/*/css/checkout.css',
					FS_DIR_APP . 'frontend/templates/*/css/framework.css',
					FS_DIR_APP . 'frontend/templates/*/css/printable.css',
					FS_DIR_APP . 'frontend/templates/*/js/app.js',
				]);

			} else {

				file_put_contents(FS_DIR_APP . 'frontend/templates/default/.development', 'standard');

				perform_action('delete', [
					FS_DIR_APP . 'frontend/templates/*/css/*.min.css',
					FS_DIR_APP . 'frontend/templates/*/css/*.min.css.map',
					FS_DIR_APP . 'frontend/templates/*/less/',
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
		}

		### Scan translations #########################################

		echo "<p>Scanning installation for translations...";

		$translations = [];

		$dir_iterator = new RecursiveDirectoryIterator(FS_DIR_APP);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $file) {
			if (!preg_match('#\.php$#', $file)) continue;
			if (preg_match('#vmods/.cache/#', $file)) continue;

			$pattern = '#'. implode(['language::translate\((?:(?!\$)', '(?:(__CLASS__)?\.)?', '(?:[\'"])([^\'"]+)(?:[\'"])', '(?:,?\s+(?:[\'"])([^\'"]+)?(?:[\'"]))?', '(?:,?\s+?(?:[\'"])([^\'"]+)?(?:[\'"]))?', ')\)']) .'#';

			if (!preg_match_all($pattern, file_get_contents($file), $matches)) continue;

			for ($i=0; $i<count($matches[0]); $i++) {
				if ($matches[1][$i]) {
					$code = substr(pathinfo($file, PATHINFO_BASENAME), 0, strpos(pathinfo($file, PATHINFO_BASENAME), '.')) . $matches[2][$i];
				} else {
					$code = $matches[2][$i];
				}
				$translations[strtolower($code)] = strtr($matches[3][$i], ["\\r" => "\r", "\\n" => "\n"]);
			}

		}

		foreach ($translations as $code => $translation) {
			database::query(
				"insert ignore into ". DB_TABLE_PREFIX ."translations
				(code, text_en, html, created_at)
				values ('". database::input($code) ."', '". database::input($translation, true) ."', '". (($translation != strip_tags($translation)) ? 1 : 0) ."', '". date('Y-m-d H:i:s') ."');"
			);
		}

		echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		### Set cache breakpoint ######################################

		echo '<p>Set cache breakpoint...';

		database::query(
			"update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_settings`') ."
			set value = '". date('Y-m-d H:i:s') ."'
			where `key` = 'cache_system_breakpoint'
			limit 1;"
		);

		echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		### Create files ######################################

		echo '<p>Create file container for error logging...';

		if (file_put_contents(FS_DIR_STORAGE . 'logs/errors.log', '') !== false) {
			echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		} else {
			echo ' <span class="error">[Failed]</span></p>' . PHP_EOL . PHP_EOL;
		}

		echo '<p>Create files for vMod cache and management...';

		if (file_put_contents(FS_DIR_STORAGE . 'vmods/.installed', '') !== false
		 && file_put_contents(FS_DIR_STORAGE . 'vmods/.settings', '') !== false
		 && file_put_contents(FS_DIR_STORAGE . 'vmods/.cache/.checked', '') !== false
		 && file_put_contents(FS_DIR_STORAGE . 'vmods/.cache/.modifications', '') !== false) {
			echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		} else {
			echo ' <span class="error">[Failed]</span></p>' . PHP_EOL . PHP_EOL;
		}

		### #############################################################

		echo implode(PHP_EOL, [
			'<h2>Complete</h2>',
			'<p>Installation complete! Please delete the <strong>~/install/</strong> folder.</p>',
			'<p>You may now log in to the <a href="../'. BACKEND_ALIAS .'/">backend</a> and start configuring your store.</p>',
			'<p>Check out the <a href="https://wiki.litecart.net/" target="_blank">LiteCart Wiki</a> website for some great tips. Turn to our <a href="https://www.litecart.net/forums/" target="_blank">Community Forums</a> if you have questions.</p>',
		]);

		if ($_SERVER['SERVER_SOFTWARE'] != 'CLI') {
			echo implode(PHP_EOL, [
				'<form method="get" action="http://x.com/intent/tweet" target="_blank">',
				'  <input type="hidden" value="https://www.litecart.net/">',
				'  <label class="form-group">',
				'    <div class="input-group">',
				'      <input type="text" class="form-input" name="text" value="Woohoo! I just installed #LiteCart and I am super excited! :)">',
				'      <button class="btn btn-primary" type="submit">Tweet!</button>',
				'    </div>',
				'  </label>',
				'</form>',
			]);
		}

	} catch (Exception $e) {
		echo implode(PHP_EOL, [
			'',
			'[ABORTED] ' . $e->getMessage(),
			'',
		]);
	}

	if (!empty($_REQUEST['redirect'])) {
		header('Location: '. $_REQUEST['redirect']);
		exit;
	}

	echo ob_get_clean();

	if ($_SERVER['SERVER_SOFTWARE'] == 'CLI') exit;

	require __DIR__ . '/includes/footer.inc.php';
