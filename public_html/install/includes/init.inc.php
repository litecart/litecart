<?php

	ini_set('display_errors', 'On');
	ini_set('memory_limit', '64M');
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');

	define('BACKEND_ALIAS', 'admin');
	define('DB_TABLE_PREFIX', 'lc_');

	// Filesystem constants (redefined safely if already set).
	if (!defined('DOCUMENT_ROOT')) {
		define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/'));
	}

	if (!defined('FS_DIR_APP')) {
		define('FS_DIR_APP', rtrim(str_replace('\\', '/', realpath(__DIR__ . '/../..')), '/') . '/');
	}

	if (!defined('FS_DIR_STORAGE')) {
		define('FS_DIR_STORAGE', __DIR__ . '/../storage/');
	}

	if (!defined('WS_DIR_APP')) {
		define('WS_DIR_APP', preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', FS_DIR_APP));
	}

	if (!defined('WS_DIR_STORAGE')) {
		define('WS_DIR_STORAGE', preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', FS_DIR_STORAGE));
	}

	require_once __DIR__ . '/../../includes/compatibility.inc.php';

	define('VMOD_DISABLED', 'true');
	require_once __DIR__ . '/../../includes/streams/stream_app.inc.php';
	stream_wrapper_register('app', 'stream_app');

	require_once __DIR__ . '/../../includes/nodes/nod_vmod.inc.php';
	require_once __DIR__ . '/../../includes/autoloader.inc.php';
	require_once __DIR__ . '/../../includes/error_handler.inc.php';
	require_once __DIR__ . '/../../includes/shorthand.inc.php';

	require_once __DIR__ . '/functions.inc.php';

	// AC-1: block access if installation is already completed.
	if (install_is_locked()) {
		csp_send_headers();
		install_reject_locked();
	}

	/**
		* Returns true when the installation is marked complete.
		* A present storage/install.lock file is authoritative; file size and
		* contents are irrelevant — only existence counts.
		*/
	function install_is_locked() {
		return is_file(FS_DIR_STORAGE . 'install.lock');
	}

	/**
		* Terminate the current request with an HTTP 403 and a short,
		* non-sensitive message. Used by installer entry points that detect
		* a completed installation.
		*/
	function install_reject_locked() {
		if (!headers_sent()) {
			http_response_code(403);
			header('Content-Type: text/plain; charset=UTF-8');
		}
		echo 'Installation already completed. Remove storage/install.lock to reinstall.' . PHP_EOL;
		exit;
	}
