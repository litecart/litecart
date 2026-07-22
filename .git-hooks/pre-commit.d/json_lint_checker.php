#!/usr/bin/env php
<?php

	declare(strict_types=1);

	$staged_output = shell_exec('git diff --cached --name-only 2>&1') ?? '';
	$staged_files = preg_split('#(\r\n?|\n)#', $staged_output, -1, PREG_SPLIT_NO_EMPTY) ?: [];
	$json_files = preg_grep('#\.json$#', $staged_files);

	if (!$json_files) {
		exit;
	}

	echo implode(PHP_EOL, [
		'',
		'-------------------------------------',
		'-- JSON Lint Checker Pre-Commit Hook --',
		'-------------------------------------',
		''
	]);

	foreach ($json_files as $file) {

		// Check if file is a JSON file
		if (!preg_match('#\.json$#', $file)) continue;

		echo '- ' . $file;

		// Get staged content from the index
		$content = shell_exec('git cat-file blob ' . escapeshellarg(':' . $file));
		if (!is_string($content)) {
			echo ' [ERROR]' . PHP_EOL;
			echo ' - Unable to read staged file content for linting.' . PHP_EOL;
			exit(1);
		}

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
