<?php

	### Storage ###################################################

	echo '<p>Set up storage folder... ';

	if (file_exists(FS_DIR_STORAGE) || mkdir(FS_DIR_STORAGE, 0777)) {
		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} else {
		throw new Exception('<span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL);
	}

	### Logs ###################################################

	echo '<p>Set up logs folder... ';

	if (file_exists(FS_DIR_STORAGE . 'logs/') || mkdir(FS_DIR_STORAGE . 'logs/', 0777)) {
		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} else {
		throw new Exception('<span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL);
	}

	file_put_contents(FS_DIR_STORAGE . 'logs/errors.log', '');
	ini_set('error_log', FS_DIR_STORAGE . 'logs/errors.log');
