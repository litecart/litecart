<?php

	### Config > Write ############################################

	echo '<p>Writing config file... ';

	$placeholders = [
		'STORAGE_FOLDER', 'ADMIN_FOLDER',
		'DB_SERVER', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'DB_TABLE_PREFIX',
		'CLIENT_IP', 'STORE_TIME_ZONE', 'HMAC_KEY_REMEMBER_ME',
	];

	// Check mandatory parameters are present
	foreach ($placeholders as $key) {
		if (!array_key_exists($key, $values)) {
			throw new Exception('Missing a value for placeholder key "' . $key . '"');
		}
	}

	// Get config template
	if (!($config_template_path = dirname(__DIR__, 2) . '/config')) {
		throw new Exception('Could not determine config template path');
	}

	$template = file_get_contents($config_template_path);
	if ($template === false) {
		throw new Exception('Could not read config template at ' . $config_template_path);
	}

	// Replace placeholders in template with values, escaping quotes to avoid breaking the config file syntax. Note that we use addcslashes() to escape single and double quotes, but not backslashes, because the config template uses single-quoted strings and we don't want to double-escape backslashes.
	$values = [
		'STORAGE_FOLDER'        => 'storage',
		'ADMIN_FOLDER'          => BACKEND_ALIAS,
		'DB_SERVER'             => DB_SERVER,
		'DB_USERNAME'           => DB_USERNAME,
		'DB_PASSWORD'           => DB_PASSWORD,
		'DB_DATABASE'           => DB_DATABASE,
		'DB_TABLE_PREFIX'       => DB_TABLE_PREFIX,
		'CLIENT_IP'             => $_REQUEST['client_ip'] ? filter_var($_REQUEST['client_ip'], FILTER_VALIDATE_IP) : '127.0.0.1',
		'STORE_TIME_ZONE'       => $_REQUEST['timezone'],
		'HMAC_KEY_REMEMBER_ME'  => bin2hex(random_bytes(32)),
	];

	$values = array_map(function($value) {
		return addcslashes($value, "\\'\"");
	}, $values);

	$config = $template;
	foreach ($values as $key => $value) {
		$config = strtr($config, $values);
	}

	// Write config file
	if (file_put_contents(FS_DIR_STORAGE . 'config.inc.php', $config) !== false) {
		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	} else {
		throw new Exception('<span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL);
	}
