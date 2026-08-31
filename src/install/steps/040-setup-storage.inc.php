<?php

	### Storage ###################################################

	try {

		echo '<p>Set up storage folder... ';

		if (!file_exists(FS_DIR_STORAGE) && !mkdir(FS_DIR_STORAGE, 0777)) {
			throw new Exception('Could not create storage folder');
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} catch (Exception $e) {
		echo '<span class="error">[Error]</span>' . PHP_EOL
		.  '<div class="error-message">'. $e->getMessage() .'</div></p>' . PHP_EOL . PHP_EOL;	}

	### Logs ###################################################

	try {

		echo '<p>Set up logs folder... ';

		if (!file_exists(FS_DIR_STORAGE . 'logs/') && !mkdir(FS_DIR_STORAGE . 'logs/', 0777)) {
			throw new Exception('Could not create logs folder');
		}

		file_put_contents(FS_DIR_STORAGE . 'logs/errors.log', '');
		ini_set('error_log', FS_DIR_STORAGE . 'logs/errors.log');

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} catch (Exception $e) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $e->getMessage() .'</div></p>',
			'',
			''
		]);
	}
