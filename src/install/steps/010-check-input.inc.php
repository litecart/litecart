<?php

	### Parameters > Check ########################################

	echo '<p>Checking installation parameters...';

	// Set default values for optional parameters
	foreach ([
		'db_server' => $_REQUEST['db_server'] ?? '127.0.0.1',
		'db_password' => $_REQUEST['db_password'] ?? '',
		'db_collation' => $_REQUEST['db_collation'] ?? 'utf8mb4_swedish_ci',
		'db_table_prefix' => $_REQUEST['db_table_prefix'] ?? 'lc_',
		'username' => $_REQUEST['username'] ?? 'admin',
		'password' => $_REQUEST['password'] ?? '',
		'client_ip' => $_REQUEST['client_ip'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
		'timezone' => $_REQUEST['timezone'] ?? ini_get('date.timezone'),
		'backend_alias' => $_REQUEST['backend_alias'] ?? 'admin',
	] as $parameter => $value) {
		$_REQUEST[$parameter] = $value;
	}

	// Validate

	if (empty($_REQUEST['db_username'])) {
		throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'No MySQL/MariaDB user provided</p>' . PHP_EOL  . PHP_EOL);
	}

	if (empty($_REQUEST['db_database'])) {
		throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'No MySQL/MariaDB database provided</p>' . PHP_EOL  . PHP_EOL);
	}

	if (empty($_REQUEST['country_code'])) {
		throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'No country code provided</p>' . PHP_EOL  . PHP_EOL);
	}

	if (!preg_match('#^[A-Z]{2}$#', $_REQUEST['country_code'])) {
		throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'Invalid country code provided</p>' . PHP_EOL  . PHP_EOL);
	}

	if (empty($_REQUEST['timezone'])) {
		throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'No time zone provided</p>' . PHP_EOL  . PHP_EOL);
	}

	if ($_REQUEST['backend_alias'] != basename($_REQUEST['backend_alias'])) {
		throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'Invalid backend folder name</p>' . PHP_EOL  . PHP_EOL);
	}

	if (empty($_REQUEST['client_ip']) || !filter_var($_REQUEST['client_ip'], FILTER_VALIDATE_IP)) {
		throw new Exception('Missing or invalid client IP address provided');
	}

	define('BACKEND_ALIAS', $_REQUEST['backend_alias']);
	define('DB_SERVER', $_REQUEST['db_server']);
	define('DB_USERNAME', $_REQUEST['db_username']);
	define('DB_PASSWORD', $_REQUEST['db_password']);
	define('DB_DATABASE', $_REQUEST['db_database']);
	define('DB_TABLE_PREFIX', $_REQUEST['db_table_prefix']);

	echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
