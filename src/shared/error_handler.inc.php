<?php

	function error_handler($errno, $errstr, $errfile, $errline, $backtraces=[]) {

		if (!(error_reporting() & $errno)) return;

		foreach ([
			FS_DIR_STORAGE => 'storage://',
			FS_DIR_APP => 'app://',
		] as $search => $replace) {
			$errfile = preg_replace('#^'. preg_quote($search, '#') .'#', $replace, str_replace('\\', '/', $errfile));
			$vmod_cache_file = 'storage://vmods/.cache/'. str_replace('/', '-', preg_replace('#^app://#', '', $errfile));
			if (is_file($vmod_cache_file)) {
				$errfile = $vmod_cache_file;
			}
		}

		$output = [];

		switch ($errno) {

			case 2048: // Equals E_STRICT but deprecated in PHP 8.4
				$output[] = '<div class="php-feedback error">❌ <strong>Strict:</strong> <pre style="white-space:pre-wrap;"> '. htmlspecialchars($errstr) .' </pre> in <strong>$errfile</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			case E_NOTICE:
			case E_USER_NOTICE:
				$output[] = '<div class="php-feedback notice">ℹ️ <strong>Notice:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			case E_WARNING:
			case E_USER_WARNING:
			case E_COMPILE_WARNING:
			case E_RECOVERABLE_ERROR:
				$output[] = '<div class="php-feedback warning">⚠️ <strong>Warning:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$output[] = '<div class="php-feedback notice">ℹ️ <strong>Deprecated:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			case E_PARSE:
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case 256: // Equals E_USER_ERROR but deprecated in PHP 8.4
				$output[] = '<div class="php-feedback error">❌ <strong>Fatal Error:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			default:
				$output[] = '<div class="php-feedback error">❌ <strong>Unknown Error:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;
		}

		if ($backtraces) {

			$output[] = 'Backtrace:';

			// Extract trace from exception_handler
			if (!empty($backtraces[0]['function']) && $backtraces[0]['function'] == 'exception_handler' && !empty($backtraces[0]['args'][0])) {
				$backtraces = array_slice($backtraces[0]['args'][0]->getTrace(), 1);
			}

			foreach ($backtraces as $trace) {

				if (empty($trace['file'])) continue;

				foreach ([
					FS_DIR_STORAGE => 'storage://',
					FS_DIR_APP => 'app://',
				] as $search => $replace) {
					$trace['file'] = preg_replace('#^'. preg_quote($search, '#') .'#', $replace, str_replace('\\', '/', $trace['file']));
					$vmod_cache_file = 'storage://vmods/.cache/'. str_replace('/', '-', preg_replace('#^app://#', '', $trace['file']));
					if (is_file($vmod_cache_file)) {
						$trace['file'] = $vmod_cache_file;
					}
				}

				$output[] = "<div> ↪ <strong>$trace[file]</strong> on line <strong>$trace[line]</strong> in <strong>$trace[function]()</strong></div>";
			}
		}

		$is_cli = (isset($_SERVER['SERVER_SOFTWARE']) && $_SERVER['SERVER_SOFTWARE'] == 'CLI');

		// Set the response code before any output is sent, headers can not be changed afterwards
		if (in_array($errno, [E_PARSE, E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, 256])) {
			if (!$is_cli && !headers_sent()) {
				http_response_code(500);
			}
		}

		// Display errors
		if (filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
			if (filter_var(ini_get('html_errors'), FILTER_VALIDATE_BOOLEAN) && !$is_cli) {
				echo isset($_GET['debug']) ? implode(PHP_EOL, $output) : $output[0]; // HTML
			} else {
				echo html_entity_decode(strip_tags(
					isset($_GET['debug']) ? implode(PHP_EOL, $output) : $output[0] // Plain text
				));
			}
		}

		if (filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN)) {

			// Redaction helpers: sensitive parameter values in  argv / REQUEST_URI / HTTP_REFERER are replaced before the line hits error_log.
			// error_handler may run before the autoloader in some paths.
			if (!function_exists('redact_query_string')) {
				include_once __DIR__ . '/functions/func_redact.inc.php';
			}

			$remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
			$hostname = '';

			if ($remote_addr) {
				try {
					$hostname = function_exists('reverse_dns') ? reverse_dns($remote_addr) : gethostbyaddr($remote_addr);
				} catch (Throwable $hostname_error) {
					// Do nothing
				}
			}

			$output = array_merge($output, array_filter([
				$is_cli ? 'Command: '. redact_argv_line($GLOBALS['argv']) : '',
				!empty($_SERVER['REQUEST_URI']) ? 'Request: '. $_SERVER['REQUEST_METHOD'] .' '. redact_query_string($_SERVER['REQUEST_URI']) .' '. $_SERVER['SERVER_PROTOCOL'] : '',
				!empty($_SERVER['HTTP_HOST']) ? 'Host: '. $_SERVER['HTTP_HOST'] : '',
				!empty($remote_addr) ? 'Client: '. $remote_addr .' ('. $hostname .')' : '',
				!empty($_SERVER['HTTP_USER_AGENT']) ? 'User Agent: '. $_SERVER['HTTP_USER_AGENT'] : '',
				!empty($_SERVER['HTTP_REFERER']) ? 'Referer: '. redact_query_string($_SERVER['HTTP_REFERER']) : '',
			]));

			if (defined('SCRIPT_TIMESTAMP_START')) {
				$output[] = 'Elapsed Time: '. number_format((microtime(true) - SCRIPT_TIMESTAMP_START) * 1000, 0, '.', ' ') .' ms';
			}

			if (defined('PLATFORM_NAME') && defined('PLATFORM_VERSION')) {
				$output[] = 'Platform: '. PLATFORM_NAME .'/'. PLATFORM_VERSION;
			}

			error_log(html_entity_decode(strip_tags(
				implode(PHP_EOL, $output))) . PHP_EOL
			);
		}

		if (in_array($errno, [E_PARSE, E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, 256])) {
			exit(1);
		}
	}

	set_error_handler(function($errno, $errstr, $errfile, $errline) {
		$backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);
		error_handler($errno, $errstr, $errfile, $errline, $backtrace);
	}, E_ALL);

	set_exception_handler(function($e) {
		$backtrace = array_slice($e->getTrace(), 1);
		error_handler(E_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $backtrace);
	});
