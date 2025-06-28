<?php

	define('PLATFORM_NAME', 'LiteCart');
	define('PLATFORM_VERSION', '3.0.0');
	define('SCRIPT_TIMESTAMP_START', microtime(true));

	// Get config
	if (!defined('FS_DIR_APP')) {
		if (!file_exists(__DIR__ . '/../storage/config.inc.php')) {
			header('Location: ./install/');
			exit;
		}
		require_once __DIR__ . '/../storage/config.inc.php';
	}

	// Capture output to buffer
	ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

	// Virtual File System
	require_once FS_DIR_APP .'includes/streams/stream_app.inc.php';
	stream_wrapper_register('app', 'stream_app');

	require_once FS_DIR_APP .'includes/streams/stream_storage.inc.php';
	stream_wrapper_register('storage', 'stream_storage');

	// Virtual Modification System
	require FS_DIR_APP .'includes/nodes/nod_vmod.inc.php';
	vmod::init();

	// Compatibility and Polyfills
	require_once 'app://includes/compatibility.inc.php';

	// 3rd party autoloader (If present)
	if (is_file(FS_DIR_APP . 'vendor/autoload.php')) {
		require FS_DIR_APP . 'vendor/autoload.php';
	}

	// Autoloader
	require_once 'app://includes/autoloader.inc.php';

	// Set error handler
	require_once 'app://includes/error_handler.inc.php';

	// General functions
	require_once 'app://includes/functions.inc.php';

	// Load shorthand functions
	require_once 'app://includes/shorthand.inc.php';

	// Jump-start some nodes
	class_exists('notices');
	class_exists('stats');

	// Run operations before capture
	event::fire('before_capture');

	stats::$data['before_content'] = microtime(true) - SCRIPT_TIMESTAMP_START;

	stats::start_watch('content_capture');
