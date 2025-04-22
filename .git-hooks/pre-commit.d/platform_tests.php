<?php

	include_once __DIR__.'/../../public_html/includes/app_header.inc.php';

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);


	$directory = functions::file_resolve_path(__DIR__.'/../../tests/');

	$files = functions::file_search($directory . '/*.php');

	echo 'Found '. count($files) . ' test files' . PHP_EOL;
	echo implode(PHP_EOL, array_map(function($file) {
		return ' - '. basename($file);
	}, $files)) . PHP_EOL;

	foreach ($files as $file) {

		echo 'Running tests from '. basename($file) .'...';

		try {

		$result = require $file;

		if ($result === true) {
			echo ' [OK]' . PHP_EOL;
		} else {
			echo ' [FAIL]' . PHP_EOL;
			exit(1);
		}

		} catch (Error $e) {
			echo ' [ERROR] ' . $e->getMessage() .' in '. $e->getFile() .' on line '. $e->getLine() . PHP_EOL;
			exit(1);

		} catch (Exception $e) {
			echo ' [EXCEPTION] ' . $e->getMessage() . PHP_EOL;
			exit(1);
		}
	}
