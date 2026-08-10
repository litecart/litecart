<?php

	/*
		LiteCart Installer — front controller. Dispatches to a page in pages/.

		Web entry points (URLs preserved):
		  /install/            -> pages/index.inc.php
		  /install/index.php   -> pages/index.inc.php
		  /install/install.php -> pages/install.inc.php
		  /install/upgrade.php -> pages/upgrade.inc.php

		CLI entry points:
		  php index.php install [options]
		  php index.php upgrade [options]
	*/

	$page = 'index';

	if (!isset($_SERVER['REQUEST_METHOD'])) { // Don't rely on php_sapi_name()

		$usage = implode(PHP_EOL, [
			'',
			'LiteCart Installer',
			'Usage: php index.php [command] [options]',
			'',
			'Commands:',
			'  install    Install LiteCart',
			'  upgrade    Upgrade LiteCart',
			'',
			'Run "php index.php install --help" or "php index.php upgrade --help" for a list of options.',
			'',
		]);

		// The command (install|upgrade) is consumed here so the page still
		// sees its options at $argv[1] — getopt() treats $argv[0] as the
		// script name and stops parsing at the first non-option argument.
		$command = $argv[1] ?? null;
		array_shift($argv);
		$_SERVER['argv'] = $argv;

		if ($command === null || in_array($command, ['help', '-h', '--help', '/?'], true)) {
			echo $usage;
			exit;

		} else if (in_array($command, ['install', 'upgrade'], true)) {
			$page = $command;

		} else {
			fwrite(STDERR, 'Unknown command: '. $command . PHP_EOL . PHP_EOL);
			fwrite(STDERR, $usage);
			exit(1);
		}

	} else if (preg_match('#/(install|upgrade)\.php$#', parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', $matches)) {
		$page = $matches[1];
	}

	require_once __DIR__ . '/init.inc.php';

	// CLI output is produced by the pages themselves (plain text, no layout).
	// Web output is captured here and wrapped in the shared layout.
	if (!is_cli()) {
		ob_start(function ($buffer) {
			$layout = require __DIR__ . '/template/layouts/default.inc.php';
			return str_replace('{{content}}', $buffer, $layout);
		});
	}

	require __DIR__ . '/pages/' . $page . '.inc.php';
