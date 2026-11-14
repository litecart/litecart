<?php

	// Log entries carry a timestamp prefix; PHP prepends the native format for error_log(), the database writer uses [Y-m-d H:i:s e]
	$entry_timestamp_pattern = '#\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} [^\]\n]+|\d{1,2}-[a-zA-Z]+-\d{4} \d{2}:\d{2}:\d{2} [a-zA-Z/_]+)\](?: ?)#';

	// Splits the log into entries with character ranges, so occurrences can be grouped and removed
	$parse_error_log = function(string $content, int $fallback_timestamp = 0) use ($entry_timestamp_pattern) {

		$entries = [];

		$matches = [];
		preg_match_all($entry_timestamp_pattern, $content, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

		if (!$matches) {

			// Content logged before entries got a timestamp prefix
			if (trim($content) !== '') {
				$entries[] = [
					'start' => 0,
					'end' => strlen($content),
					'timestamp' => $fallback_timestamp,
					'error' => trim($content),
					'backtrace' => trim($content),
				];
			}

			return $entries;
		}

		// Content logged before the first entry got a timestamp prefix
		$leading = substr($content, 0, $matches[0][0][1]);

		if (trim($leading) !== '') {
			$leading_lines = preg_split('#(?:\r\n?|\n)#', $leading);
			$entries[] = [
				'start' => 0,
				'end' => $matches[0][0][1],
				'timestamp' => $fallback_timestamp,
				'error' => trim($leading_lines[0]),
				'backtrace' => trim(implode(PHP_EOL, $leading_lines)),
			];
		}

		foreach ($matches as $index => $match) {

			// A timestamp prefix may sit mid-line when the previous entry lost its trailing newline
			$start = $match[0][1];
			$body_start = $start + strlen($match[0][0]);
			$end = isset($matches[$index + 1]) ? $matches[$index + 1][0][1] : strlen($content);

			$body = substr($content, $body_start, $end - $body_start);
			$lines = preg_split('#(?:\r\n?|\n)#', $body);

			$entries[] = [
				'start' => $start,
				'end' => $end,
				'timestamp' => strtotime($match[1][0]) ?: $fallback_timestamp,
				'error' => trim($lines[0]),
				'backtrace' => trim(implode(PHP_EOL, array_slice($lines, 1))),
			];
		}

		return $entries;
	};

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
		'function' => function($params) use ($parse_error_log) {

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

			$entries = $parse_error_log(file_get_contents($log_file), (int)filemtime($log_file));

			$errors = [];

			foreach ($entries as $entry) {

				if ($entry['error'] === '') continue;

				$checksum = crc32($entry['error']);

				if (!isset($errors[$checksum])) {
					$errors[$checksum] = [
						'error' => $entry['error'],
						'backtrace' => $entry['backtrace'] ?: null,
						'occurrences' => 1,
						'timestamp' => $entry['timestamp'],
						'critical' => (bool)preg_match('#(Parse|Fatal) error:#is', $entry['error']),
					];
				} else {
					$errors[$checksum]['occurrences']++;
					$errors[$checksum]['timestamp'] = max($errors[$checksum]['timestamp'], $entry['timestamp']);
				}
			}

			// Sort: most recently seen first, or most frequently occurring first.
			if ($sort_by === 'frequent') {
				uasort($errors, function($a, $b) {
					if ($a['occurrences'] != $b['occurrences']) return $a['occurrences'] > $b['occurrences'] ? -1 : 1;
					return $b['timestamp'] <=> $a['timestamp'];
				});
			} else {
				uasort($errors, function($a, $b) {
					return $b['timestamp'] <=> $a['timestamp'];
				});
			}

			if ($critical_only) {
				$errors = array_filter($errors, fn($e) => $e['critical']);
			}

			$total = count($errors);
			$output = [];

			foreach (array_slice($errors, 0, $limit) as $error) {
				$output[] = [
					'checksum' => crc32($error['error']),
					'error' => $error['error'],
					'backtrace' => $error['backtrace'],
					'occurrences' => $error['occurrences'],
					'last_occurrence' => date('Y-m-d H:i:s', $error['timestamp']),
					'critical' => $error['critical'],
				];
			}

			ini_set('memory_limit', $initial_memory_limit); // Restore limit

			return [
				'sort_by' => $sort_by,
				'total' => $total,
				'returned' => count($output),
				'log_file' => $log_file,
				'errors' => $output,
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
		'function' => function($params) use ($parse_error_log) {

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

			$content = file_get_contents($log_file);
			$entries = $parse_error_log($content, (int)filemtime($log_file));

			$ranges = [];
			$occurrences = 0;

			foreach ($entries as $entry) {

				if ($entry['error'] !== $error) continue;

				$occurrences++;
				$ranges[] = [$entry['start'], $entry['end']];
			}

			if (!$occurrences) {
				ini_set('memory_limit', $initial_memory_limit); // Restore limit
				return ['deleted' => false, 'message' => 'Error not found in log'];
			}

			// Remove from the end backwards so the earlier offsets stay valid
			for ($index = count($ranges) - 1; $index >= 0; $index--) {
				$content = substr($content, 0, $ranges[$index][0]) . substr($content, $ranges[$index][1]);
			}

			file_put_contents($log_file, $content);

			ini_set('memory_limit', $initial_memory_limit); // Restore limit

			return [
				'deleted' => true,
				'occurrences_removed' => $occurrences,
				'message' => "Removed $occurrences occurrence(s) from the error log",
			];
		}
	];

	return $toolset;
