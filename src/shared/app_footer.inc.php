<?php

	stats::stop_watch('content_capture');

	stats::start_watch('after_content');

	// Store the captured output buffer
	document::$content = ob_get_contents();
	ob_clean();

	// Run after capture processes
	event::fire('after_capture');

	// Run prepare output processes
	event::fire('prepare_output');

	// Run before output processes
	event::fire('before_output');

	stats::stop_watch('after_content');

	// Output page
	echo document::render();

	// Run after processes
	event::fire('shutdown');

	// Execute background jobs
	if (!($last_push = settings::get('jobs_last_push')) || strtotime($last_push) < strtotime('-15 minutes')) {
		try {

			// To avoid using this push method, set up a cron job to call every 5 minutes for the following command:
			// Example: */5 * * * * php -d register_argc_argv=on /path/to/your/catalog/index.php push_jobs &>/dev/null

			database::query(
				"update ". DB_PREFIX ."settings
				set `value` = '". date('Y-m-d H:i:s') ."'
				where `key` = 'jobs_last_push'
				limit 1;"
			);

			$disabled_functions = f::string_split(ini_get('disable_functions'));
			$url = document::ilink('f:push_jobs');

			if (!in_array('exec', $disabled_functions)) {

				// Windows has no /dev/null and treats & as a command separator instead of a background operator
				$null_device = (PHP_OS_FAMILY === 'Windows') ? 'NUL' : '/dev/null';

				$command = 'php -d register_argc_argv=on '. escapeshellarg(realpath(FS_DIR_APP .'index.php')) .' push_jobs > '. $null_device .' 2>&1';

				exec(PHP_OS_FAMILY === 'Windows' ? 'start /B cmd /c '. $command : $command .' &');

			} else if (!in_array('fsockopen', $disabled_functions) && !in_array('stream_socket_client', $disabled_functions)) {
				(new http_client)->set_timeout(3)->call('GET', $url, asynchronous: true);

			} else {
				throw new Exception('Unable to execute background jobs. Please set up a cron job to call the following command every 5 minutes: php -d register_argc_argv=on '. FS_DIR_APP .'index.php push_jobs');
			}

		} catch (Throwable $e) {
			// Ignore errors
		}
	}
