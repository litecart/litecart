<?php

	include_once __DIR__.'/../../public_html/includes/app_header.inc.php';

	ini_set('display_errors', 1);

	$directory = functions::file_resolve_path(__DIR__.'/../../tests/');

	$files = functions::file_search($directory . '/*.php');

	echo 'Found '. count($files) . ' test files' . PHP_EOL;

	foreach ($files as $file) {

		echo 'Running tests from '. basename($file) .'...';

		$result = include $file;

		if ($result === true) {
			echo ' [OK]' . PHP_EOL;
		} else {
			echo ' [Failed]' . PHP_EOL;
			exit(1);
		}
	}
