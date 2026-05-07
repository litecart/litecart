#!/usr/bin/env php
<?php

	declare(strict_types=1);

	echo PHP_EOL;

	$tracked_files = preg_split('#(\r\n?|\n)#', shell_exec('git ls-files'), -1, PREG_SPLIT_NO_EMPTY);
	$staged_files = preg_split('#(\r\n?|\n)#', shell_exec('git diff --cached --name-only 2>&1'), -1, PREG_SPLIT_NO_EMPTY);
	$files_to_check = preg_grep('#\.php$#', $staged_files);

	if ($files_to_check) {
		echo implode(PHP_EOL, [
			'',
			'-------------------------------------',
			'-- PHP Lint Checker Pre-Commit Hook --',
			'-------------------------------------',
			''
		]);
	}

	foreach ($files_to_check as $file) {

		echo '- ' . $file;

		// Get the staged content of the file
		$tmp_file = tempnam(sys_get_temp_dir(), '_php_lint');
		shell_exec('git cat-file blob :'. $file .' > '. $tmp_file);

		// Check if content has valid PHP syntax
		exec('php -l '. escapeshellarg($tmp_file) .' 2>&1', $output, $result_code);

		// Remove temporary file
		unlink($tmp_file);

		// Handle result
		if (!empty($result_code)) {
			echo ' [ERROR]' . PHP_EOL;
			echo '  - ' . $file . ' contains syntax errors!' . PHP_EOL;
			echo '  - ' . implode(PHP_EOL, $output) . PHP_EOL;
			exit($result_code);
		}

		echo ' [OK]' . PHP_EOL;
	}
