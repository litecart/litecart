<?php


	$staged_files = preg_split('#(\r\n?|\n)#', shell_exec('git diff --cached --name-only 2>&1'), -1, PREG_SPLIT_NO_EMPTY);
	$files_to_check = preg_grep('#\.json$#', $staged_files);
	var_dump($files_to_check);

	if ($files_to_check) {
		echo implode(PHP_EOL, [
			'',
			'-------------------------------------',
			'-- JSON Lint Checker Pre-Commit Hook --',
			'-------------------------------------',
			''
		]);
	}

	foreach ($files_to_check as $file) {

		// Check if file is a JSON file
		if (!preg_match('#\.json$#', $file)) continue;

		echo '- ' . $file;

		// Get the staged content of the file
		$tmp_file = tempnam(sys_get_temp_dir(), '_json_lint');
		shell_exec('git cat-file blob :' . $file . ' > ' . $tmp_file);
		$content = file_get_contents($tmp_file);

		// Remove temporary file
		unlink($tmp_file);

		// Check if content is valid JSON
		$decoded = json_decode($content, true);

		// Handle result
		if (json_last_error() !== JSON_ERROR_NONE) {
			echo ' [ERROR]';
			echo ' - '. $file . ' contains invalid JSON!' . PHP_EOL;
			echo ' - JSON Error: ' . json_last_error_msg() . PHP_EOL;
			exit(1);
		}

		echo ' [OK]' . PHP_EOL;
	}
