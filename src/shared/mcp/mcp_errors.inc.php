<?php

	$toolset = [
		'name' => 'errors',
		'description' => 'Tools for viewing and managing PHP error log entries.',
		'tools' => [],
	];

	$toolset['tools'][] = [
		'name' => 'list_errors',
		'description' => 'Returns PHP error log entries grouped by unique message. Each entry includes the error text, optional backtrace, occurrence count, last seen timestamp, and whether it is a fatal/parse error. Use sort_by to get the most recent errors or the most frequently occurring ones.',
		'inputSchema' => [
			'type' => 'object',
			'properties' => [
				'sort_by' => [
					'type' => 'string',
					'description' => 'Sort order: "recent" for the most recently seen errors first, or "frequent" for the most occurring errors first (default: "recent")',
					'enum' => ['recent', 'frequent'],
				],
				'limit' => [
					'type' => 'integer',
					'description' => 'Max number of unique errors to return (default: 10, max: 200)',
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

			$sort_by = $params['sort_by'] ?? 'recent';
			if (!in_array($sort_by, ['recent', 'frequent'], true)) $sort_by = 'recent';

			$limit = min((int)($params['limit'] ?? 10), 200);
			if ($limit < 1) $limit = 10;

			$critical_only = !empty($params['critical_only']);

			$initial_memory_limit = ini_get('memory_limit');
			ini_set('memory_limit', -1); // Unlimit memory for reading log file

			$entries = preg_replace('#(\r\n?|\n)#', PHP_EOL, file_get_contents($log_file));

			$errors = [];

			if (preg_match_all('#\[(\d{1,2}-[a-zA-Z]+-\d{4} \d\d\:\d\d\:\d\d [a-zA-Z/]+)\] ([^\r\n]+)(.*?)(?=\R*\[|\R*$)#us', $entries, $matches)) {

				foreach (array_keys($matches[0]) as $i) {

					$checksum = crc32($matches[2][$i]);
					$backtrace = trim(preg_replace('#(\r\n?|\n)+#', '$1', $matches[3][$i]), "\r\n");
					$timestamp = strtotime($matches[1][$i]) ?: 0;

					if (!isset($errors[$checksum])) {
					  $errors[$checksum] = [
					    'checksum' => $checksum,
					    'error' => $matches[2][$i],
					    'backtrace' => $backtrace ?: null,
					    'occurrences' => 1,
					    'last_occurrence' => date('Y-m-d H:i:s', $timestamp),
					    'critical' => (bool)preg_match('#(Parse|Fatal) error:#is', $matches[2][$i]),
					  ];
					} else {
					  $errors[$checksum]['occurrences']++;
					  $errors[$checksum]['last_occurrence'] = date('Y-m-d H:i:s', $timestamp);
					}
				}
			}

			unset($entries);
			ini_set('memory_limit', $initial_memory_limit); // Restore limit

			// Sort: most recently seen first, or most frequently occurring first.
			// last_occurrence is a fixed Y-m-d H:i:s format, so string compare is chronological.
			if ($sort_by === 'frequent') {
				uasort($errors, function($a, $b) {
					if ($a['occurrences'] != $b['occurrences']) return $a['occurrences'] > $b['occurrences'] ? -1 : 1;
					return strcmp($b['last_occurrence'], $a['last_occurrence']);
				});
			} else {
				uasort($errors, function($a, $b) {
					return strcmp($b['last_occurrence'], $a['last_occurrence']);
				});
			}

			if ($critical_only) {
				$errors = array_filter($errors, fn($e) => $e['critical']);
			}

			$total = count($errors);

			return [
				'sort_by' => $sort_by,
				'total' => $total,
				'returned' => min($total, $limit),
				'log_file' => $log_file,
				'errors' => array_values(array_slice($errors, 0, $limit)),
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