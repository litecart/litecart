<?php

	/*!
		LiteCart® 3.0.0

		This application is provided free without warranty.

		Author:   LiteCart <development@litecart.net>
		License:  CC BY-ND 4.0 https://creativecommons.org/licenses/by-nd/4.0/
		Website:  https://www.litecart.net/

		LiteCart is a registered trademark, property of T. Almroth, LiteCart AB.
	*/

	require_once __DIR__ . '/includes/app_header.inc.php';

	// Process a CLI request
	if (is_cli()) {

		if (!isset($argv[1]) || (in_array($argv[1], ['help', '-h', '--help', '/?']))) {
			echo implode(PHP_EOL, [
				'',
				PLATFORM_NAME .'® '. PLATFORM_VERSION,
				'Copyright (c) '. date('Y') .' LiteCart AB',
				'https://www.litecart.net/',
				'Usage: php '. basename(__FILE__) .' [command]',
				'',
				'Command:',
				'  push_jobs          Run the background jobs',
				'  navigate {uri}     Navigate to a specific URI',
				'',
			]);
			exit;
		}

		switch ($argv[1]) {

			case 'push_jobs':

				// Run the background jobs
				require_once 'app://frontend/pages/push_jobs.inc.php';
				exit;

			case 'navigate':

				// Navigate to a specific URI
				if (empty($argv[2])) {
					echo 'Error: Missing URI argument.' . PHP_EOL;
					exit(1);
				}

				$_SERVER['REQUEST_URI'] = $argv[2];

				break;

			default:
				echo 'Unknown command: '. $argv[1] . PHP_EOL;
				echo 'Run "php '. basename(__FILE__) .' help" for a list of commands.' . PHP_EOL;
				exit(1);
		}
	}

	// Recognize some destinations
	route::load('app://frontend/routes/url_*.inc.php');
	route::load('app://backend/routes/url_*.inc.php');

	// Append a route for last destination
	route::add('*', [
		'pattern' => '#^.+$#',
		'endpoint' => 'frontend',
		'controller' => 'app://frontend/pages/$0.inc.php',
	]);

	// Find destination for the current request
	route::identify();

	// Initialize endpoint
	if (!empty(route::$selected['endpoint']) && route::$selected['endpoint'] == 'backend') {
		require 'app://backend/init.inc.php';
	} else {
		require 'app://frontend/init.inc.php';
	}

	// Process the route
	route::process();

	// Run operations after processing the route
	require_once 'app://includes/app_footer.inc.php';
