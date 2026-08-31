<?php

	### App > Check Version ################################################

	try {

		echo '<p>Checking application database version... ';

		if ($version_detected) {
			echo PLATFORM_DATABASE_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} else if (!empty($_REQUEST['from_version'])) {
			echo $_REQUEST['from_version'] .' (User Defined) <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			throw new Exception('Undetected');
		}

	} catch (Throwable $t) {
		echo ' '. implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $t->getMessage() .'</div></p>',
			'',
			'',
		]);
	}
