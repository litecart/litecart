<?php

	function error_handler($errno, $errstr, $errfile, $errline) {

		if (!(error_reporting() & $errno)) return;

		foreach ([
			FS_DIR_STORAGE => 'storage://',
			FS_DIR_APP => 'app://',
		] as $search => $replace) {
			$errfile = preg_replace('#^'. preg_quote($search, '#') .'#', $replace, str_replace('\\', '/', $errfile));
		}

		$output = [];

		switch ($errno) {

			case 2048: // Equals E_STRICT but deprecated in PHP 8.4
				$output[] = '<div class="php-feedback error"><strong>Strict:</strong> <pre style="white-space:pre-wrap;"> '. htmlspecialchars($errstr) .' </pre> in <strong>$errfile</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			case E_NOTICE:
			case E_USER_NOTICE:
				$output[] = '<div class="php-feedback notice"><strong>Notice:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			case E_WARNING:
			case E_USER_WARNING:
			case E_COMPILE_WARNING:
			case E_RECOVERABLE_ERROR:
				$output[] = '<div class="php-feedback warning"><strong>Warning:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$output[] = '<div class="php-feedback notice"><strong>Deprecated:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			case E_PARSE:
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case 256: // Equals E_USER_ERROR but deprecated in PHP 8.4
				$output[] = '<div class="php-feedback error"><strong>Fatal error:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;

			default:
				$output[] = '<div class="php-feedback error"><strong>Fatal error:</strong> <pre style="white-space:pre-wrap;"> <quote>'. htmlspecialchars($errstr) .'</quote> </pre> in <strong>'. $errfile .'</strong> on line <strong>'. (int)$errline .'</strong></div>';
				break;
		}

		if ($backtraces = debug_backtrace()) {

			// Remove self from backtrace
			array_shift($backtraces);

			// Extract trace from exception_handler
			if (!empty($backtraces[0]['function']) && $backtraces[0]['function'] == 'exception_handler') {
				$backtraces = array_slice($backtraces[0]['args'][0]->getTrace(), 1);
			}

			foreach ($backtraces as $backtrace) {

				if (empty($backtrace['file'])) continue;

				foreach ([
					FS_DIR_STORAGE => 'storage://',
					FS_DIR_APP => 'app://',
				] as $search => $replace) {
					$backtrace['file'] = preg_replace('#^'. preg_quote($search, '#') .'#', $replace, str_replace('\\', '/', $backtrace['file']));
				}

				$output[] = "<div> → <strong>$backtrace[file]</strong> on line <strong>$backtrace[line]</strong> in <strong>$backtrace[function]()</strong></div>";
			}
		}

		// Display errors
		if (filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
			if (filter_var(ini_get('html_errors'), FILTER_VALIDATE_BOOLEAN) && $_SERVER['SERVER_SOFTWARE'] != 'CLI') {
				echo isset($_GET['debug']) ? implode('<br>'.PHP_EOL, $output) : $output[0]; // HTML
			} else {
				echo html_entity_decode(strip_tags(
					isset($_GET['debug']) ? implode('<br>'.PHP_EOL, $output) : $output[0] // Plain text
				));
			}
		}

		if (filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN)) {

			$output = array_merge($output, array_filter([
				($_SERVER['SERVER_SOFTWARE'] == 'CLI') ? 'Command: '. implode(' ', $GLOBALS['argv']) : '',
				!empty($_SERVER['REQUEST_URI']) ? 'Request: '. $_SERVER['REQUEST_METHOD'] .' '. $_SERVER['REQUEST_URI'] .' '. $_SERVER['SERVER_PROTOCOL'] : '',
				!empty($_SERVER['HTTP_HOST']) ? 'Host: '. $_SERVER['HTTP_HOST'] : '',
				!empty($_SERVER['REMOTE_ADDR']) ? 'Client: '. $_SERVER['REMOTE_ADDR'] .' ('. gethostbyaddr($_SERVER['REMOTE_ADDR']) .')' : '',
				!empty($_SERVER['HTTP_USER_AGENT']) ? 'User Agent: '. $_SERVER['HTTP_USER_AGENT'] : '',
				!empty($_SERVER['HTTP_REFERER']) ? 'Referer: '. $_SERVER['HTTP_REFERER'] : '',
				!empty($_SERVER['HTTP_REFERER']) ? 'Referer: '. $_SERVER['HTTP_REFERER'] : '',
			]));

			if (defined('SCRIPT_TIMESTAMP_START')) {
				$output[] = 'Elapsed Time: '. number_format((microtime(true) - SCRIPT_TIMESTAMP_START) * 1000, 0, '.', ' ') .' ms';
			}

			$output[] = 'Platform: '. PLATFORM_NAME .'/'. PLATFORM_VERSION;

			error_log(html_entity_decode(strip_tags(
				implode(PHP_EOL, $output))) . PHP_EOL
			);
		}

		if (in_array($errno, [E_PARSE, E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, E_USER_ERROR])) {
			http_response_code(500);
			exit(500);
		}
	}

	set_error_handler('error_handler');

	// Pass fatal errors to error handler
	function exception_handler($e) {
		error_handler(E_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
	}

	set_exception_handler('exception_handler');
