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

			case E_NOTICE:
			case E_USER_NOTICE:
				$output[] = "<strong>Notice:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong>";
				break;

			case E_WARNING:
			case E_USER_WARNING:
			case E_COMPILE_WARNING:
			case E_RECOVERABLE_ERROR:
				$output[] = "<strong>Warning:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong>";
				break;

			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$output[] = "<strong>Deprecated:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong>";
				break;

			case E_PARSE:
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$output[] = "<strong>Fatal error:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong>";
				break;

			default:
				$output[] = "<strong>Fatal error:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong>";
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
				$backtrace['file'] = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', 'app://', functions::file_realpath($backtrace['file']));
				$output[] = " → <strong>$backtrace[file]</strong> on line <strong>$backtrace[line]</strong> in <strong>$backtrace[function]()</strong>";
			}
		}

		// Display errors
		if (filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {

			if (isset($_GET['debug'])) {

				if (filter_var(ini_get('html_errors'), FILTER_VALIDATE_BOOLEAN)) {
					echo implode(PHP_EOL . '<br>' . PHP_EOL, $output);
				} else {
					echo html_entity_decode(strip_tags(implode(PHP_EOL, $output)));
				}

			} else {

				if (($_SERVER['SERVER_SOFTWARE'] == 'CLI') && filter_var(ini_get('html_errors'), FILTER_VALIDATE_BOOLEAN)) {
					echo $output[0] . PHP_EOL;
				} else {
					echo html_entity_decode(strip_tags($output[0])) . PHP_EOL;
				}
			}
		}

		if (filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN)) {
			$output = implode(PHP_EOL, array_merge($output, array_filter([
				($_SERVER['SERVER_SOFTWARE'] == 'CLI') ? 'Command: '. implode(' ', $GLOBALS['argv']) : '',
				!empty($_SERVER['REQUEST_URI']) ? 'Request: '. $_SERVER['REQUEST_METHOD'] .' '. $_SERVER['REQUEST_URI'] .' '. $_SERVER['SERVER_PROTOCOL'] : '',
				!empty($_SERVER['HTTP_HOST']) ? 'Host: '. $_SERVER['HTTP_HOST'] : '',
				!empty($_SERVER['REMOTE_ADDR']) ? 'Client: '. $_SERVER['REMOTE_ADDR'] .' ('. gethostbyaddr($_SERVER['REMOTE_ADDR']) .')' : '',
				!empty($_SERVER['HTTP_USER_AGENT']) ? 'User Agent: '. $_SERVER['HTTP_USER_AGENT'] : '',
				!empty($_SERVER['HTTP_REFERER']) ? 'Referer: '. $_SERVER['HTTP_REFERER'] : '',
				'Platform: '. PLATFORM_NAME .'/'. PLATFORM_VERSION,
			])));

			error_log(html_entity_decode(strip_tags($output)) . PHP_EOL);
		}

		if (in_array($errno, [E_PARSE, E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, E_USER_ERROR])) {
			http_response_code(500);
			exit;
		}
	}

	set_error_handler('error_handler');

	// Pass fatal errors to error handler
	function exception_handler($e) {
		error_handler(E_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
	}

	set_exception_handler('exception_handler');
