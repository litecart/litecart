<?php

	### Files > Default Data ######################################

	try {

		echo '<p>Copying default files...</p>' . PHP_EOL;

		perform_action('copy', [
			__DIR__.'/../data/default/src/' => FS_DIR_APP,
			__DIR__.'/../data/default/storage/' => FS_DIR_STORAGE,
		]);

		echo PHP_EOL;

	} catch (Exception $e) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span></p>',
			'<div class="error-message">'. $e->getMessage() .'</div>',
			'',
			'',
		]);
	}

	### .htaccess mod rewrite #####################################

	try {

		echo '<p>Setting mod_rewrite base path... ';

		$htaccess = file_get_contents(__DIR__.'/../htaccess');

		$htaccess = strtr($htaccess, [
			'{WS_DIR_APP}' => WS_DIR_APP,
			'{FS_DIR_APP}' => FS_DIR_APP,
		]);

		if (file_put_contents(FS_DIR_APP . '.htaccess', $htaccess) === false) {
			throw new Exception('Could not write .htaccess');
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} catch (Exception $e) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $e->getMessage() .'</div></p>',
			'',
			'',
		]);
	}
