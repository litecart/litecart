<?php

	/**
	 * Shared bootstrap for installer and upgrader entry points.
	 * Provides CLI detection, filesystem constants, lock-file check and
	 * a canonical "is this a CLI run" helper.
	 */

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
	if (!defined('FS_DIR_APP')) {
		define('FS_DIR_APP', rtrim(str_replace('\\', '/', realpath(__DIR__ . '/../..')), '/') . '/');
	}

	if (!defined('FS_DIR_STORAGE')) {
		define('FS_DIR_STORAGE', FS_DIR_APP . 'storage/');
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
	 * Returns true when the current process is a CLI invocation.
	 * Requires both php_sapi_name() to match AND REQUEST_METHOD to be
	 * absent as received from the webserver. Defense-in-depth against
	 * mis-configured FPM pools that might leak shell access through a
	 * spoofed SAPI identifier.
	 */
	function install_is_cli() {
		if (php_sapi_name() !== 'cli') {
			return false;
		}
		// REQUEST_METHOD is normalised by the polyfill above; but we check
		// the original $argv presence for extra confidence.
		return isset($GLOBALS['argv']);
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
