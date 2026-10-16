<?php

	$toolset = [
		'name' => 'errors',
		'description' => 'Tools for viewing and managing PHP error log entries.',
		'tools' => [],
	];

	$toolset['tools'][] = [
		'name' => 'list_errors',
		'description' => 'Returns PHP error log entries grouped by unique message. Each entry includes the error text, optional backtrace, occurrence count, last seen timestamp, and whether it is a fatal/parse error.',
		'inputSchema' => [
			'type' => 'object',
			'properties' => [
				'limit' => [
					'type' => 'integer',
					'description' => 'Max number of unique errors to return (default: 50, max: 200)',
				],
				'critical_only' => [
					'type' => 'boolean',
					'description' => 'When true, return only fatal/parse errors (default: false)',
				],
			],
			'required' => [],
		],
		'function' => function($params) {

				$log_file = ini_get('error_log');

				if (!$log_file || !is_readable($log_file)) {
					return [
						'total' => 0,
						'returned' => 0,
						'log_file' => $log_file ?: null,
						'errors' => [],
					];
				}

				$limit = min((int)($params['limit'] ?? 50), 200);
				if ($limit < 1) $limit = 50;

				$critical_only = !empty($params['critical_only']);

				$initial_memory_limit = ini_get('memory_limit');
				ini_set('memory_limit', -1); // Unlimit memory for reading log file

				$entries = preg_replace('#(\r\n?|\n)#', PHP_EOL, file_get_contents($log_file));

				$errors = [];

				if (preg_match_all('#\[(\d{1,2}-[a-zA-Z]+-\d{4} \d\d\:\d\d\:\d\d [a-zA-Z/]+)\] ([^\r\n]+)(.*?)(?=\R*\[|\R*$)#us', $entries, $matches)) {

					foreach (array_keys($matches[0]) as $i) {

						$checksum = crc32($matches[2][$i]);
						$backtrace = trim(preg_replace('#(\r\n?|\n)+#', '$1', $matches[3][$i]), "\r\n");

						if (!isset($errors[$checksum])) {
						  $errors[$checksum] = [
						    'checksum' => $checksum,
						    'error' => $matches[2][$i],
						    'backtrace' => $backtrace ?: null,
						    'occurrences' => 1,
						    'last_occurrence' => date('Y-m-d H:i:s', strtotime($matches[1][$i])),
						    'critical' => (bool)preg_match('#(Parse|Fatal) error:#is', $matches[2][$i]),
						  ];
						} else {
						  $errors[$checksum]['occurrences']++;
						  $errors[$checksum]['last_occurrence'] = date('Y-m-d H:i:s', strtotime($matches[1][$i]));
						}
					}
				}

				unset($entries);
				ini_set('memory_limit', $initial_memory_limit); // Restore limit

				// Sort: critical first, then by occurrences desc, then by last_occurrence desc
				uasort($errors, function($a, $b) {
					if ($a['critical'] != $b['critical']) return $a['critical'] ? -1 : 1;
					if ($a['occurrences'] != $b['occurrences']) return $a['occurrences'] > $b['occurrences'] ? -1 : 1;
					return strcmp($b['last_occurrence'], $a['last_occurrence']);
				});

				if ($critical_only) {
					$errors = array_filter($errors, fn($e) => $e['critical']);
				}

				$total = count($errors);
				$errors = array_values(array_slice($errors, 0, $limit));

				return [
					'total' => $total,
					'returned' => count($errors),
					'log_file' => $log_file,
					'errors' => $errors,
				];
			}
	];

	// MCP Tool: Delete all occurrences of a specific error from the PHP error log
	$toolset['tools'][] = [
		'name' => 'clear_errors',
		'description' => 'Clear all occurrences of a specific error from the PHP error log. Pass the exact "error" string returned by list_errors.',
		'inputSchema' => [
			'type' => 'object',
			'properties' => [
				'error' => [
					'type' => 'string',
					'description' => 'Exact error message text to delete (the "error" field from list_errors)',
				],
			],
			'required' => ['error'],
		],
		'function' => function($params) {

			$log_file = ini_get('error_log');

			if (!$log_file) {
				return ['deleted' => false, 'message' => 'No error log configured'];
			}

			if (!is_writable($log_file)) {
				return ['deleted' => false, 'message' => 'Error log is not writable'];
			}

			$error = $params['error'];

			$initial_memory_limit = ini_get('memory_limit');
			ini_set('memory_limit', -1); // Unlimit memory for reading log file

			$content = preg_replace('#(\r\n?|\n)#', PHP_EOL, file_get_contents($log_file));

			$content = preg_replace(
				'#\[\d{1,2}-[a-zA-Z]+-\d{4} \d\d\:\d\d\:\d\d [a-zA-Z/]+\] '. preg_quote($error, '#') . addcslashes(PHP_EOL, "\r\n") .'[^\[]*#s',
				'',
				$content,
				-1,
				$count
			);

			ini_set('memory_limit', $initial_memory_limit); // Restore limit

			if (!$count) {
				return ['deleted' => false, 'message' => 'Error not found in log'];
			}

			file_put_contents($log_file, $content);

			return [
				'deleted' => true,
				'occurrences_removed' => $count,
				'message' => "Removed $count occurrence(s) from the error log",
			];
		}
	];

	return $toolset;
