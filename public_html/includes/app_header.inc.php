<?php

	define('PLATFORM_NAME', 'LiteCart');
	define('PLATFORM_VERSION', '3.0.0');
	define('SCRIPT_TIMESTAMP_START', microtime(true));

	// Get config
	if (!defined('FS_DIR_APP')) {
		if (!file_exists(__DIR__ . '/../storage/config.inc.php')) {
			redirect('./install/', 302);
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

	// Load shorthand functions
	require_once 'app://includes/shorthand.inc.php';

	// 3rd party autoloader (If present)
	if (is_file(FS_DIR_APP . 'vendor/autoload.php')) {
		require_once FS_DIR_APP . 'vendor/autoload.php';
	}

	// Autoloader
	require_once 'app://includes/autoloader.inc.php';

	// Set error handler
	require_once 'app://includes/error_handler.inc.php';

	// Jump-start some nodes
	class_exists('notices');
	class_exists('stats');

	// CSRF protection for state-changing requests
	if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS']) && !empty(session::$data['id'])) {

		// Excluded paths (payment gateway callbacks, MCP endpoints)
		$csrf_excluded_paths = ['order_process', 'mcp'];
		$csrf_skip = false;
		$request_path = strtok($_SERVER['REQUEST_URI'], '?');
		foreach ($csrf_excluded_paths as $path) {
			if (preg_match('#/' . preg_quote($path, '#') . '(?:/|$)#', $request_path)) {
				$csrf_skip = true;
				break;
			}
		}

		if (!$csrf_skip) {
			$submitted_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
			if (!hash_equals(session::csrf_token(), $submitted_token)) {
				http_response_code(403);
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
					header('Content-Type: application/json');
					echo json_encode(['error' => 'CSRF token mismatch. Please reload the page and try again.']);
				} else {
					echo '<h1>403 Forbidden</h1><p>CSRF token mismatch. Please <a href="javascript:history.back()">go back</a> and try again.</p>';
				}
				exit;
			}
		}
	}

	// Run operations before capture
	event::fire('before_capture');

	stats::$data['before_content'] = microtime(true) - SCRIPT_TIMESTAMP_START;

	stats::start_watch('content_capture');
