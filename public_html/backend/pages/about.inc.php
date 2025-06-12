<?php

	breadcrumbs::reset();
	breadcrumbs::add(language::translate('title_dashboard', 'Dashboard'), WS_DIR_ADMIN);
	breadcrumbs::add(language::translate('title_about', 'About'), document::link());

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['errors'])) {
				throw new Exception(language::translate('error_must_select_errors', 'You must select errors'));
			}

			$log_file = ini_get('error_log');

			ini_set('memory_limit', -1); // Unlimit memory for reading log file
			$content = preg_replace('#(\r\n?|\n)#', PHP_EOL, file_get_contents($log_file));

			foreach ($_POST['errors'] as $error) {
				$content = preg_replace('#\[\d{1,2}-[a-zA-Z]+-\d{4} \d\d\:\d\d\:\d\d [a-zA-Z/]+\] '. preg_quote($error, '#') . addcslashes(PHP_EOL, "\r\n") .'[^\[]*#s', '', $content, -1, $count);
				if (!$count) {
					throw new Exception('Failed deleting error from log');
				}
			}

			file_put_contents($log_file, $content);

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// CPU Usage
	if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
		if (function_exists('sys_getloadavg')) {
			$cpu_usage = round(sys_getloadavg()[0], 2);
		}
	}

	// Memory Usage
	if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {

		if (@is_readable('/proc/meminfo')) {
			$fh = fopen('/proc/meminfo','r');

			while ($line = fgets($fh)) {
				$pieces = [];
				if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
					$ram_usage = $pieces[1];
					continue;
				}
				if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces)) {
					$ram_free = $pieces[1];
					continue;
				}
			}

			fclose($fh);

			$ram_usage = round($ram_usage / ($ram_usage + $ram_free) * 100, 2);
		}
	}

	// Server Uptime
	if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
		if (@is_readable('/proc/uptime')) {
			$raw_uptime = round((float)file_get_contents('/proc/uptime'));
			$seconds = fmod($raw_uptime, 60);  $raw_uptime = intdiv($raw_uptime, 60);
			$minutes = $raw_uptime % 60;  $raw_uptime = intdiv($raw_uptime, 60);
			$hours = $raw_uptime % 24;  $raw_uptime = intdiv($raw_uptime, 24);
			$days = $raw_uptime;

			if ($days) {
				$uptime = $days .' day(s)';
			} else if ($hours) {
				$uptime = $hours .' hour(s)';
			} else if ($minutes) {
				$iptime = $minutes .' minute(s)';
			} else if ($seconds) {
				$uptime = $seconds .' second(s)';
			}
		}
	}

	// Errors
	$errors = [];

	if ($log_file = ini_get('error_log')) {

		if (($filesize = filesize($log_file)) > 1024e6) {
			notices::add('warnings', language::translate('warning_truncating_extremely_large_log_file', 'Truncating an extremely large log file') .' ('. language::number_format($filesize / (1024 * 1024)) .' Mbytes)');
			file_put_contents($logfile, '');
		}

		$iniatial_memory_limit = ini_get('memory_limit');
		ini_set('memory_limit', -1); // Unlimit memory for reading log file

		$entries = preg_replace('#(\r\n?|\n)#', PHP_EOL, file_get_contents($log_file));

		if (preg_match_all('#\[(\d{1,2}-[a-zA-Z]+-\d{4} \d\d\:\d\d\:\d\d [a-zA-Z/]+)\] (.*?)'. addcslashes(PHP_EOL, "\r\n") .'([^\[]*)#s', $entries, $matches)) {

			foreach (array_keys($matches[0]) as $i) {

				$checksum = crc32($matches[2][$i]);

				if (!isset($errors[$checksum])) {
					$errors[$checksum] = [
						'error' => $matches[2][$i],
						'backtrace' => $matches[3][$i],
						'occurrences' => 1,
						'last_occurrence' => strtotime($matches[1][$i]),
						'critical' => preg_match('#(Parse|Fatal) error:#s', $matches[2][$i]) ? true : false,
					];
				} else {
					$errors[$checksum]['occurrences']++;
					//$rows[$checksum]['backtrace'] = $matches[3][$i];
					$errors[$checksum]['last_occurrence'] = strtotime($matches[1][$i]);
				}
			}
		}

		uasort($errors, function($a, $b) {

			if ($a['critical'] != $b['critical']) {
				return ($a['critical'] > $b['critical']) ? -1 : 1;
			}

			if ($a['occurrences'] != $b['occurrences']) {
				return ($a['occurrences'] > $b['occurrences']) ? -1 : 1;
			}

			return ($a['last_occurrence'] > $b['last_occurrence']) ? -1 : 1;
		});

		unset($entries);

		ini_set('memory_limit', $iniatial_memory_limit); // Restore limit
	}

	// Render view
	$_page = new ent_view('app://backend/template/pages/about.inc.php');

	$_page->snippets = [
		'machine' => [
			'name' => php_uname('n'),
			'architecture' => php_uname('m'),
			'os' => [
				'name' => php_uname('s') .' '. php_uname('r'),
				'version' => php_uname('v'),
			],
			'ip_address' => $_SERVER['SERVER_ADDR'],
			'hostname' => gethostbyaddr($_SERVER['SERVER_ADDR']),
			'cpu_usage' => fallback($cpu_usage, ''),
			'memory_usage' => fallback($memory_usage, ''),
			'uptime' =>  fallback($uptime, ''),
		],
		'web_server' => [
			'name' => fallback($_SERVER['SERVER_SOFTWARE'], ''),
			'sapi' => php_sapi_name(),
			'current_user' => get_current_user(),
			'loaded_modules' => function_exists('apache_get_modules') ? apache_get_modules() : [],
		],
		'php' => [
			'version' => PHP_VERSION .' ('. ((PHP_INT_SIZE === 8) ? '64-bit' : '32-bit') .')',
			'whoami' => (function_exists('exec') && !in_array('exec', preg_split('#\s*,\s*#', ini_get('disabled_functions')))) ? exec('whoami') : '',
			'loaded_extensions' => (function_exists('get_loaded_extensions') && !in_array('get_loaded_extensions', preg_split('#\s*,\s*#', ini_get('disabled_functions')))) ? get_loaded_extensions() : [],
			'disabled_functions' => ini_get('disabled_functions') ?  preg_split('#\s*,\s*#', ini_get('disabled_functions')) : [],
			'memory_limit' => ini_get('memory_limit'),
			'ini' => ini_get_all(null, false),
		],
		'database' => [
			'name' => database::server_info(),
			'library' => mysqli_get_client_info(),
			'hostname' => DB_SERVER,
			'user' => DB_USERNAME,
			'database' => DB_DATABASE,
		],
		'errors' => $errors,
	];

	sort($_page->snippets['php']['loaded_extensions'], SORT_NATURAL);
	sort($_page->snippets['php']['disabled_functions'], SORT_NATURAL);

	echo $_page;
