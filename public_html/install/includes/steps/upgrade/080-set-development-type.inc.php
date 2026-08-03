<?php

	### Installer > Set Development Type ########################################

	if (!is_dir(__DIR__.'/../../../../../.git')) {

		echo '<p>Preparing CSS files...</p>' . PHP_EOL . PHP_EOL;

		perform_action('delete', [
			FS_DIR_APP . 'backend/template/scss/',
		]);

		if (!empty($_REQUEST['development_type']) && $_REQUEST['development_type'] == 'advanced') {

			file_put_contents(FS_DIR_APP . 'includes/templates/default/.development', 'advanced');

			perform_action('delete', [
				FS_DIR_APP . 'frontend/templates/*/css/app.css',
				FS_DIR_APP . 'frontend/templates/*/css/checkout.css',
				FS_DIR_APP . 'frontend/templates/*/css/framework.css',
				FS_DIR_APP . 'frontend/templates/*/css/printable.css',
				FS_DIR_APP . 'frontend/templates/*/js/app.js',
			]);

		} else {

			file_put_contents(FS_DIR_APP . 'includes/templates/default/.development', 'standard');

			perform_action('delete', [
				FS_DIR_APP . 'frontend/templates/*/css/*.min.css',
				FS_DIR_APP . 'frontend/templates/*/css/*.min.css.map',
				FS_DIR_APP . 'frontend/templates/*/scss/',
				FS_DIR_APP . 'frontened/templates/default/js/*.min.js.map',
				FS_DIR_APP . 'frontend/templates/default/scss/',
			]);

			perform_action('modify', [
				FS_DIR_APP . 'frontend/templates/*/layouts/*.inc.php' => [
					['search' => 'app.min.css',       'replace' => 'app.css'],
					['search' => 'checkout.min.css',  'replace' => 'checkout.css'],
					['search' => 'framework.min.css', 'replace' => 'framework.css'],
					['search' => 'printable.min.css', 'replace' => 'printable.css'],
					['search' => 'app.min.js',        'replace' => 'app.js'],
				],
			]);
		}

		echo PHP_EOL;
	}
