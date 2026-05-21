<?php

	// Check version
	if (version_compare(phpversion(), '8.0.0', '<') == true) {
		die('This application requires at minimum PHP 8.0+ (Detected '. phpversion() .')');
	}

	// Polyfill for glob brace on Alpine
	if (!defined('GLOB_BRACE')) {
		define('GLOB_BRACE', 0);
	}

	// Polyfill for getallheaders() on non-Apache machines
	if (!function_exists('getallheaders')) {
		function getallheaders() {
			$headers = [];
			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
			return $headers;
		}
	}

	// Polyfill for some $_SERVER variables in CLI
	if (!isset($_SERVER['REQUEST_METHOD'])) { // Don't rely on php_sapi_name()
		$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__.'/..');
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'localhost';
		$_SERVER['SERVER_PORT'] = '80';
		$_SERVER['SERVER_PROTOCOL'] = 'https';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/';
		$_SERVER['SERVER_SOFTWARE'] = 'CLI';
		$_SERVER['SCRIPT_FILENAME'] = $argv[0] ?? 'index.php';
		$_SERVER['HTTPS'] = 'on';
	}

	// Normalize Windows paths to Unix-style
	$_SERVER['SCRIPT_FILENAME'] = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);

	if (!isset($_SERVER['SERVER_SOFTWARE'])) {
		$_SERVER['SERVER_SOFTWARE'] = 'Unknown';
	}

	if (empty($_SERVER['HTTPS'])) {
		$_SERVER['HTTPS'] = ($_SERVER['SERVER_PROTOCOL'] == 'https') ? 'on' : 'off';
	}

	if (empty($_SERVER['HTTP_HOST'])) {
		$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
	}

	if (!isset($_SERVER['HTTP_USER_AGENT'])) {
		$_SERVER['HTTP_USER_AGENT'] = '';
	}

/*
	// Proxy users (enable only if these headers are passed safely by your server stack)
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		$_SERVER['SERVER_PROTOCOL'] = 'https';
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_PORT'] = 443;
	}

	foreach([
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_REAL_IP',
		'HTTP_CF_CONNECTING_IP',
	] as $header) {
		if (isset($_SERVER[$header])) {
			foreach (array_reverse(preg_split('#\s*,\s*#', $_SERVER[$header], -1, PREG_SPLIT_NO_EMPTY)) as $ip) {
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
					$_SERVER['REMOTE_ADDR'] = $ip;
				}
			}
		}
	}
*/
