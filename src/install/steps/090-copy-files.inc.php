<?php

	### Files > Default Data ######################################

	echo '<p>Copying default files...</p>' . PHP_EOL;

	perform_action('copy', [
		__DIR__.'/../data/default/src/' => FS_DIR_APP,
		__DIR__.'/../data/default/storage/' => FS_DIR_STORAGE,
	]);

	echo PHP_EOL;

	### .htaccess mod rewrite #####################################

	echo '<p>Setting mod_rewrite base path...';

	$htaccess = file_get_contents(__DIR__.'/../htaccess');

	$htaccess = strtr($htaccess, [
		'{WS_DIR_APP}' => WS_DIR_APP,
		'{FS_DIR_APP}' => FS_DIR_APP,
	]);

	if (file_put_contents(FS_DIR_APP . '.htaccess', $htaccess)) {
		echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	} else {
		echo ' <span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL;
	}
