<?php

	if (is_dir(__DIR__.'/../../../.git')) return;

	### Files > Development Type ##################################

	try {

		echo '<p>Preparing CSS files...</p>' . PHP_EOL;

		perform_action('delete', [
			FS_DIR_APP . 'backend/template/scss/',
		]);

		if (!empty($_REQUEST['development_type']) && $_REQUEST['development_type'] == 'advanced') {

			file_put_contents(FS_DIR_APP . 'frontend/templates/default/.development', 'advanced');

			perform_action('delete', [
				FS_DIR_APP . 'assets/litecore/css/email.css',
				FS_DIR_APP . 'assets/litecore/css/framework.css',
				FS_DIR_APP . 'assets/litecore/css/printable.css',
				FS_DIR_APP . 'assets/litecore/js/framework.js',
				FS_DIR_APP . 'frontend/templates/*/css/app.css',
				FS_DIR_APP . 'frontend/templates/*/css/checkout.css',
				FS_DIR_APP . 'frontend/templates/*/js/app.js',
			]);

		} else {

			file_put_contents(FS_DIR_APP . 'frontend/templates/default/.development', 'standard');

			perform_action('delete', [
				FS_DIR_APP . 'assets/litecore/css/*.min.css',
				FS_DIR_APP . 'assets/litecore/css/*.min.css.map',
				FS_DIR_APP . 'assets/litecore/css/*.min.css.map',
				FS_DIR_APP . 'assets/litecore/scss/',
				FS_DIR_APP . 'frontend/templates/*/css/*.min.css',
				FS_DIR_APP . 'frontend/templates/*/css/*.min.css.map',
				FS_DIR_APP . 'frontend/templates/*/scss/',
			]);

			perform_action('modify', [
				FS_DIR_APP . 'backend/template/layouts/*.inc.php' => [
					['search' => 'framework.min.js',  'replace' => 'framework.js'],
					['search' => 'printable.min.js',  'replace' => 'printable.js'],
				],
			]);

			perform_action('modify', [
				FS_DIR_APP . 'frontend/templates/*/layouts/*.inc.php' => [
					['search' => 'app.min.css',       'replace' => 'app.css'],
					['search' => 'checkout.min.css',  'replace' => 'checkout.css'],
					['search' => 'email.min.css',     'replace' => 'email.css'],
					['search' => 'framework.min.css', 'replace' => 'framework.css'],
					['search' => 'printable.min.css', 'replace' => 'printable.css'],
					['search' => 'app.min.js',        'replace' => 'app.js'],
					['search' => 'framework.min.js',  'replace' => 'framework.js'],
				],
			]);
		}

		echo PHP_EOL;

	} catch (Exception $e) {
		echo implode(PHP_EOL, [
			'<p>Preparing CSS files... <span class="error">[Error]</span></p>',
			'<div class="error-message">'. $e->getMessage() .'</div>',
			'',
			'',
		]);
}
