<?php

	require_once FS_DIR_APP . '/includes/config_writer.inc.php';

	### Config > Write ############################################

	echo '<p>Writing config file... ';

	// AC-3, AC-4: values are serialised through var_export() via
	// install_render_config(), which also validates client_ip.
	$config = install_render_config(dirname(__DIR__, 2) . '/config', [
		'STORAGE_FOLDER'        => FS_DIR_APP . 'storage/',
		'ADMIN_FOLDER'          => BACKEND_ALIAS,
		'DB_SERVER'             => DB_SERVER,
		'DB_USERNAME'           => DB_USERNAME,
		'DB_PASSWORD'           => DB_PASSWORD,
		'DB_DATABASE'           => DB_DATABASE,
		'DB_TABLE_PREFIX'       => DB_TABLE_PREFIX,
		'CLIENT_IP'             => $_REQUEST['client_ip'],
		'STORE_TIME_ZONE'       => $_REQUEST['timezone'],
		'HMAC_KEY_REMEMBER_ME'  => bin2hex(random_bytes(32)),
	]);

	if (file_put_contents(FS_DIR_STORAGE . 'config.inc.php', $config) !== false) {
		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} else {
		throw new Exception('<span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL);
	}
