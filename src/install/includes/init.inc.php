<?php

	ini_set('display_errors', 'On');
	ini_set('memory_limit', '64M');
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');

	// BACKEND_ALIAS / DB_TABLE_PREFIX are NOT defined here — the install
	// page defines them from user-supplied parameters, and the upgrade page
	// picks them up from the existing storage/config.inc.php.

	// CLI polyfill: normalise $_SERVER and collect options into $_REQUEST.
	// Entry points may pass their own long-options list via $INSTALL_CLI_OPTIONS
	// before including this file; it documents the accepted options.
	//
	// Note: getopt() cannot be used here. It reads the real process argv (not
	// $_SERVER['argv'], which the router shifts) and stops at the first
	// positional argument — the "install"/"upgrade" command word would swallow
	// every option that follows it.
	if (!isset($_SERVER['REQUEST_METHOD'])) {
		$_SERVER['SERVER_SOFTWARE'] = 'CLI';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		// Parse "--name=value" / "--name" long options. argv[0] holds the
		// script name (or the command after the router has shifted it) and
		// positional arguments are ignored. Mirrors getopt()'s optional-value
		// semantics: a bare flag yields false, "--name=value" yields the value.
		$_REQUEST = [];
		foreach ($_SERVER['argv'] as $key => $arg) {
			if ($key == 0) continue;
			if (preg_match('#^--([a-z_][a-z0-9_]*)(?:=(.*))?$#', $arg, $matches)) {
				$_REQUEST[$matches[1]] = isset($matches[2]) ? $matches[2] : false;
			}
		}
	}

	// Filesystem constants (redefined safely if already set).
	if (!defined('DOCUMENT_ROOT')) {
		if (!empty($_SERVER['DOCUMENT_ROOT'])) {
			define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/'));
		} else if (!empty($_REQUEST['document_root'])) {
			define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', realpath($_REQUEST['document_root'])), '/'));
		}
		// else: entry point (install page / upgrade page) must define it before use
	}

	if (!defined('FS_DIR_APP')) {
		define('FS_DIR_APP', rtrim(str_replace('\\', '/', realpath(__DIR__ . '/../../')), '/') . '/');
	}

	if (!defined('FS_DIR_STORAGE')) {
		// No realpath() — storage/ may not exist yet on a first-time install,
		// in which case realpath() would return false and break the path.
		define('FS_DIR_STORAGE', FS_DIR_APP . 'storage/');
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
	require_once FS_DIR_APP . 'shared/compatibility.inc.php';

	// Load virtual file system but leave vMod disabled
	if (!defined('VMOD_DISABLED')) {
		define('VMOD_DISABLED', 'true');
	}

	require_once FS_DIR_APP . 'shared/streams/stream_app.inc.php';
	stream_wrapper_register('app', 'stream_app');

	require_once FS_DIR_APP . 'shared/streams/stream_storage.inc.php';
	stream_wrapper_register('storage', 'stream_storage');

	// Load other additional dependencies
	require_once FS_DIR_APP . 'shared/nodes/nod_vmod.inc.php';
	require_once FS_DIR_APP . 'shared/autoloader.inc.php';
	require_once FS_DIR_APP . 'shared/error_handler.inc.php';
	require_once FS_DIR_APP . 'shared/shorthand.inc.php';

	require_once __DIR__ . '/functions.inc.php';

	ob_start(function($buffer){

		// Layout wrapping must happen here (inside the output buffer), but
		// ent_view::render() cannot be called from inside an ob_start handler
		// — it nests its own ob_start(), which PHP forbids. Render the layout
		// inline by performing snippet substitution directly on the template.

		$snippets = [
			'{{charset}}' => mb_http_output(),
			'{{nonce}}' => htmlspecialchars(NONCE, ENT_QUOTES),
			'{$framework}' => is_file(__DIR__ . '/../../../assets/litecore/css/framework.min.css') ? '.min.css' : '.css',
			'{{year}}' => date('Y'),
			'{{ws_dir_app}}' => WS_DIR_APP,
			'{{content}}' => $buffer,
		];

		$layout = file_get_contents(FS_DIR_APP . 'install/template/layouts/default.inc.php');
		return strtr($layout, $snippets);
	});
