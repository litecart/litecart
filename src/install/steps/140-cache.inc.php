<?php

	### Set cache breakpoint ######################################

	try {

		echo '<p>Set cache breakpoint... ';

		database::query(
			"update ". str_replace('`lc_', '`'.DB_PREFIX, '`lc_settings`') ."
			set value = '". date('Y-m-d H:i:s') ."'
			where `key` = 'cache_system_breakpoint'
			limit 1;"
		);

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} catch (Throwable $t) {
		echo '<span class="error">[Error]</span>' . PHP_EOL
		.  '<div class="error-message">'. $t->getMessage() .'</div></p>' . PHP_EOL . PHP_EOL;	}

	### Create files ######################################

	try {

		echo '<p>Create file container for error logging... ';

		if (file_put_contents(FS_DIR_STORAGE . 'logs/errors.log', '') === false) {
			throw new Exception('Could not create error log file');
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} catch (Throwable $t) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span></p>',
			'<div class="error-message">'. $t->getMessage() .'</div>',
			'',
			'',
		]);
	}

	try {

		echo '<p>Create files for vMod cache and management... ';

		if (file_put_contents(FS_DIR_STORAGE . 'vmods/.installed', '') === false
		|| file_put_contents(FS_DIR_STORAGE . 'vmods/.settings', '') === false
		|| file_put_contents(FS_DIR_STORAGE . 'vmods/.cache/.checked', '') === false
		|| file_put_contents(FS_DIR_STORAGE . 'vmods/.cache/.modifications', '') === false) {
			throw new Exception('Could not create vMod cache files');
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} catch (Throwable $t) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $t->getMessage() .'</div></p>',
			'',
			'',
		]);
	}
