<?php

	### App > Check Version ################################################

	echo '<p>Checking application database version... ';

	if (defined('PLATFORM_DATABASE_VERSION')) {
		echo PLATFORM_DATABASE_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} else if (!empty($_REQUEST['from_version'])) {
		define('PLATFORM_DATABASE_VERSION', $_REQUEST['from_version']);
		echo $_REQUEST['from_version'] . ' (User Defined) <span class="warning">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} else {
		throw new Exception(' <span class="error">[Undetected]</span></p>' . PHP_EOL . PHP_EOL);
	}