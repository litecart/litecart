<?php

	define('PLATFORM_NAME', 'LiteCart');
	define('PLATFORM_VERSION', '3.0.0');
	define('SCRIPT_TIMESTAMP_START', microtime(true));

	// Get config
	if (!defined('FS_DIR_APP')) {
		if (file_exists(__DIR__ . '/../storage/config.inc.php')) {
			require_once __DIR__ . '/../storage/config.inc.php';
		} else if (!isset($_SERVER['REQUEST_METHOD'])) { // CLI
			echo 'Configuration file not found. Please run the installer.';
			exit(1);
		} else {
			header('Location: ./install/', true, 302);
			exit;
		}
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
	if ($_SERVER['SERVER_SOFTWARE'] != 'CLI' && !in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS'])) {

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
					echo f::format_json(['error' => 'CSRF token mismatch. Please reload the page and try again.']);
				} else {
					echo implode(PHP_EOL, [
						'<h1>403 Forbidden</h1>',
						'<p>CSRF token mismatch. Please <a href="javascript:history.back()">go back</a> and try again.</p>'
					]);
				}
				exit;
			}
		}
	}

	// Detect truncated POST (PHP max_input_vars exceeded)
	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$max_input_vars = (int)ini_get('max_input_vars');
		$received_vars = count($_POST, COUNT_RECURSIVE) + count($_FILES, COUNT_RECURSIVE);
		if ($max_input_vars > 0 && $received_vars >= $max_input_vars) {
			notices::add('errors', strtr(t('error_post_truncated', 'The submitted form has too many fields for the server (received :received fields, server limit :limit). Some data was not saved. Ask your hoster to increase the PHP setting max_input_vars in php.ini, .htaccess (Apache), or .user.ini.'), [':received' => $received_vars, ':limit' => $max_input_vars]));
		}
	}

	// Run operations before capture
	event::fire('before_capture');

	stats::$data['before_content'] = microtime(true) - SCRIPT_TIMESTAMP_START;

	stats::start_watch('content_capture');
