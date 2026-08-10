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

		// To avoid using this push method, set up a cron job to call every 5 minutes for the following command:
		// Example: */5 * * * * php /path/to/your/catalog/index.php push_jobs &>/dev/null

		$url = document::ilink('f:push_jobs');
		$disabled_functions = f::string_split(ini_get('disable_functions'));

		if (!in_array('exec', $disabled_functions)) {

			exec(implode('', [
				'(',
				' command -v wget >/dev/null',
				' && wget -q -O - "'. escapeshellarg($url) .'"',
				' || curl -s "'. escapeshellarg($url).'"',
				') > /dev/null 2>&1 &',
			]));

		} else if (!in_array('fsockopen', $disabled_functions)) {

			$parts = parse_url($url);
			$fp = fsockopen($parts['host'], $parts['port'] ?? 80, $errno, $errstr, 30);

			fwrite($fp, implode("\r\n", [
				'GET '. $parts['path'] .' HTTP/1.1',
				'Host: '. $parts['host'],
				'Connection: Close',
				'',
				'',
			]));

			fclose($fp);
		}
	}
