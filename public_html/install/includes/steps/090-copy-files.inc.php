<?php

	### Files > Default Data ######################################

	echo '<p>Copying default files...</p>' . PHP_EOL;

	perform_action('copy', [
		'data/default/public_html/' => FS_DIR_APP,
		'data/default/storage/' => FS_DIR_STORAGE,
	]);

	echo PHP_EOL;

	### .htaccess mod rewrite #####################################

	echo '<p>Setting mod_rewrite base path...';

	$htaccess = file_get_contents('htaccess');

	$htaccess = strtr($htaccess, [
		'{WS_DIR_APP}' => WS_DIR_APP,
		'{FS_DIR_APP}' => FS_DIR_APP,
	]);

	if (file_put_contents('../.htaccess', $htaccess)) {
		echo ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	} else {
		echo ' <span class="error">[Error]</span></p>' . PHP_EOL . PHP_EOL;
	}
