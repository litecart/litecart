<?php

	class stats {

		private static $_watches;
		public static $data;

		public static function start_watch($id) {
			if (!isset(self::$_watches[$id])) {
				self::$_watches[$id] = microtime(true);
			}
		}

		public static function stop_watch($id) {

			if (!isset(self::$_watches[$id])) {
				trigger_error('Cannot stop a non-existing timer ('. $id .')', E_USER_NOTICE);
				return;
			}

			$elapsed = microtime(true) - self::$_watches[$id];

			if (isset(self::$data[$id])) {
				self::$data[$id] += $elapsed;
			} else {
				self::$data[$id] = $elapsed;
			}

			unset(self::$_watches[$id]);
		}

		public static function render() {

			// Page parse time
			$page_parse_time = microtime(true) - SCRIPT_TIMESTAMP_START;

			$output = implode(PHP_EOL, [
				'<!--',
				'  - Cache Enabled: '. (cache::$enabled ? 'Yes' : 'No'),
				'  - Memory Peak: ' . number_format(memory_get_peak_usage(true) / 1e6, 2, '.', ' ') . ' MB / '. ini_get('memory_limit'),
				'  - Included Files: ' . count(get_included_files()),
				'  - Page Load: ' . number_format($page_parse_time * 1000, 0, '.', ' ') . ' ms',
				'    - Before Content: ' . number_format(self::$data['before_content'] * 1000, 0, '.', ' ') . ' ms',
				'    - Content Capturing: ' . number_format(self::$data['content_capture'] * 1000, 0, '.', ' ') . ' ms',
				'    - After Content: ' . number_format(self::$data['after_content'] * 1000, 0, '.', ' ') . ' ms',
				'    - Rendering: ' . number_format(self::$data['rendering'] * 1000, 0, '.', ' ') . ' ms',
				'  - Database Queries: ' . number_format(database::$stats['queries'], 0, '.', ' '),
				'  - Database Duration: ' . number_format(database::$stats['duration'] * 1000, 0, '.', ' ') . ' ms',
				'  - Network Requests: ' . number_format(http_client::$stats['requests'], 0, '.', ' '),
				'  - Network Duration: ' . number_format(http_client::$stats['duration'] * 1000, 0, '.', ' ') . ' ms',
				'  - vMod: ' . number_format(vmod::$time_elapsed * 1000, 0, '.', ' ') . ' ms',
				'-->',
			]);

			if (($page_parse_time = microtime(true) - SCRIPT_TIMESTAMP_START) > 10) {
				error_log(implode(PHP_EOL, array_filter([
					'Warning: Long script running time '. (floor($page_parse_time / 10 ) * 10) .'+ s',
					//$output,
					'Elapsed Time: '. number_format($page_parse_time, 0, '.', ' ') .' s',
					($_SERVER['SERVER_SOFTWARE'] == 'CLI') ? 'Command: '. implode(' ', $GLOBALS['argv']) : '',
					!empty($_SERVER['REQUEST_URI']) ? 'Request: '. $_SERVER['REQUEST_METHOD'] .' '. $_SERVER['REQUEST_URI'] .' '. $_SERVER['SERVER_PROTOCOL'] : '',
					!empty($_SERVER['HTTP_HOST']) ? 'Host: '. $_SERVER['HTTP_HOST'] : '',
					!empty($_SERVER['REMOTE_ADDR']) ? 'Client: '. $_SERVER['REMOTE_ADDR'] .' ('. gethostbyaddr($_SERVER['REMOTE_ADDR']) .')' : '',
					!empty($_SERVER['HTTP_USER_AGENT']) ? 'User Agent: '. $_SERVER['HTTP_USER_AGENT'] : '',
					!empty($_SERVER['HTTP_REFERER']) ? 'Referer: '. $_SERVER['HTTP_REFERER'] : '',
				])) . PHP_EOL);
			}

			if (class_exists('administrator', false) && administrator::check_login()) {
				return $output;
			}
		}
	}
