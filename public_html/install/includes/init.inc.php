<?php

	ini_set('display_errors', 'On');
	ini_set('memory_limit', '64M');
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');

	// BACKEND_ALIAS / DB_TABLE_PREFIX are NOT defined here — install.php
	// defines them from user-supplied parameters (line ~181), and upgrade.php
	// picks them up from the existing storage/config.inc.php.

	// CLI polyfill: normalise $_SERVER and collect getopt into $_REQUEST.
	// Entry points may pass their own long-options list via $INSTALL_CLI_OPTIONS
	// before including this file; fall back to an empty list.
	if (!isset($_SERVER['REQUEST_METHOD'])) {
		$_SERVER['SERVER_SOFTWARE'] = 'CLI';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		if (!empty($INSTALL_CLI_OPTIONS) && is_array($INSTALL_CLI_OPTIONS)) {
			$_REQUEST = getopt('', $INSTALL_CLI_OPTIONS);
		}
	}

	// Filesystem constants (redefined safely if already set).
	if (!defined('DOCUMENT_ROOT')) {
		if (!empty($_SERVER['DOCUMENT_ROOT'])) {
			define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/'));
		} else if (!empty($_REQUEST['document_root'])) {
			define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', realpath($_REQUEST['document_root'])), '/'));
		}
		// else: entry point (install.php / upgrade.php) must define it before use
	}

	if (!defined('FS_DIR_APP')) {
		define('FS_DIR_APP', rtrim(str_replace('\\', '/', realpath(__DIR__ . '/../..')), '/') . '/');
	}

	if (!defined('FS_DIR_STORAGE')) {
		define('FS_DIR_STORAGE', str_replace('\\', '/', realpath(__DIR__ . '/../storage/')));
	}

	if (!defined('WS_DIR_APP') && defined('DOCUMENT_ROOT')) {
		define('WS_DIR_APP', preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', FS_DIR_APP));
	}

	if (!defined('WS_DIR_STORAGE') && defined('DOCUMENT_ROOT')) {
		define('WS_DIR_STORAGE', preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', FS_DIR_STORAGE));
	}

	// Generate NONCE at the start of the request and reuse it throughout.
	if (!defined('NONCE')) {
		define('NONCE', bin2hex(random_bytes(16)));
	}

	// Polyfills
	require_once __DIR__ . '/../../includes/compatibility.inc.php';

	// Load virtual file system but leave vMod disabled
	if (!defined('VMOD_DISABLED')) {
		define('VMOD_DISABLED', 'true');
	}
	require_once __DIR__ . '/../../includes/streams/stream_app.inc.php';
	if (!in_array('app', stream_get_wrappers())) {
		stream_wrapper_register('app', 'stream_app');
	}

	require_once __DIR__ . '/../../includes/nodes/nod_vmod.inc.php';
	require_once __DIR__ . '/../../includes/autoloader.inc.php';
	require_once __DIR__ . '/../../includes/error_handler.inc.php';
	require_once __DIR__ . '/../../includes/shorthand.inc.php';

	require_once __DIR__ . '/functions.inc.php';

	// Lock-check is intentionally NOT enforced here.
	// install.php enforces it explicitly (rejects when locked).
	// upgrade.php must run *while* the lock is present (that's its job).
