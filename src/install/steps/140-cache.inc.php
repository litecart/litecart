<?php

	### Set cache breakpoint ######################################

	echo '<p>Set cache breakpoint...';

	database::query(
		"update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_settings`') ."
		set value = '". date('Y-m-d H:i:s') ."'
		where `key` = 'cache_system_breakpoint'
		limit 1;"
	);

	echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	### Create files ######################################

	echo '<p>Create file container for error logging...';

	if (file_put_contents(FS_DIR_STORAGE . 'logs/errors.log', '') !== false) {
		echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	} else {
		echo ' <span class="error">[Failed]</span></p>' . PHP_EOL . PHP_EOL;
	}

	echo '<p>Create files for vMod cache and management...';

	if (file_put_contents(FS_DIR_STORAGE . 'vmods/.installed', '') !== false
	&& file_put_contents(FS_DIR_STORAGE . 'vmods/.settings', '') !== false
	&& file_put_contents(FS_DIR_STORAGE . 'vmods/.cache/.checked', '') !== false
	&& file_put_contents(FS_DIR_STORAGE . 'vmods/.cache/.modifications', '') !== false) {
		echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	} else {
		echo ' <span class="error">[Failed]</span></p>' . PHP_EOL . PHP_EOL;
	}
