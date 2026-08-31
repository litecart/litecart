<?php

	### PHP > Check Version #######################################

	try {

		echo '<p>Checking PHP version... ';

		if (PHP_VERSION_ID < 80000) {
			throw new Exception('PHP 8.0+ minimum requirement');

		} else {
			$min_active_version = json_decode(file_get_contents('https://www.php.net/releases/active.php'), true)[0][0]['version'] ?? '';
			if (version_compare(PHP_VERSION, $min_active_version, '<')) {
				echo PHP_VERSION .' <span class="warning">[Warning] PHP '. PHP_VERSION .' has reached <a href="https://www.php.net/supported-versions.php" target="_blank">end of life</a>. Use minimum PHP '. htmlspecialchars($min_active_version) .'</span></p>' . PHP_EOL . PHP_EOL;
			} else {
				echo PHP_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
			}
		}

	} catch (Exception $e) {
		echo PHP_VERSION .' '. implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $e->getMessage() .'</div></p>',
			'',
			'',
		]);
}

	### PHP > Check PHP Extensions ###############################

	try {

		echo '<p>Checking for PHP extensions... ';

		$missing_extensions = [];

		foreach ($requirements['scripting']['php']['requiredExtensions'] as $extension) {
			if ((is_array($extension) && !in_array(true, array_map(function($ext) {
				return extension_loaded($ext);
			}, $extension)) && !extension_loaded($extension))) {
				$missing_extensions[] = $extension;
			}
		}

		$missing_extensions = array_map(function($extension) {
			return is_array($extension) ? implode(' or ', $extension) : $extension;
		}, $missing_extensions);

		if ($missing_extensions) {
			echo '<span class="warning">[Warning] Some important PHP extensions are missing ('. implode(', ', $missing_extensions) .'). It is recommended that you enable them in php.ini.</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		}

	} catch (Exception $e) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $e->getMessage() .'</div></p>',
			'',
			'',
		]);
}

	### PHP > Check Disabled Functions ############################

	try {

		echo '<p>Checking available PHP functions... ';

		$critical_functions = ['error_log', 'ini_set'];
		$important_functions = ['allow_url_fopen', 'shell_exec', 'exec', 'apache_get_modules'];

		if ($disabled_functions = array_intersect($critical_functions, preg_split('#\s*,\s*#', ini_get('disable_functions'), -1, PREG_SPLIT_NO_EMPTY))) {
			throw new Exception('Critical functions are disabled ('. implode(', ', $disabled_functions) .'). You need to unblock them in php.ini');

		} else if ($disabled_functions = array_intersect($important_functions, preg_split('#\s*,\s*#', ini_get('disable_functions'), -1, PREG_SPLIT_NO_EMPTY))) {
			echo '<span class="warning">[Warning] Some common functions are disabled ('. implode(', ', $disabled_functions) .'). It is recommended that you unblock them in php.ini.</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
		}

	} catch (Exception $e) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $e->getMessage() .'</div></p>',
			'',
			'',
		]);
}

	### PHP > Check display_errors ################################

	try {

		echo '<p>Checking PHP display_errors... ';

		if (in_array(strtolower(ini_get('display_errors')), ['1', 'true', 'on', 'yes'])) {
			echo ini_get('display_errors') . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			echo ini_get('display_errors') . ' <span class="warning">[Warning] Missing permissions to display errors?</span></p>' . PHP_EOL . PHP_EOL;
		}

	} catch (Exception $e) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $e->getMessage() .'</div></p>',
			'',
			'',
		]);
}

	### PHP > Check document root #################################

	if ($_SERVER['SERVER_SOFTWARE'] != 'CLI') {

		try {

			echo '<p>Checking $_SERVER["DOCUMENT_ROOT"]... ';

			if (DOCUMENT_ROOT . preg_replace('#/index\.php$#', '', strtok($_SERVER['REQUEST_URI'], '?')) != str_replace('\\', '/', __DIR__)) {
				echo $_SERVER['DOCUMENT_ROOT'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

			} else {
				echo $_SERVER['DOCUMENT_ROOT'] . ' <span class="warning">[Warning]</span> There is a problem with your web server configuration causing $_SERVER["DOCUMENT_ROOT"] and __DIR__ to return conflicting paths. Contact your web host and have them correcting this.</p>' . PHP_EOL  . PHP_EOL;
			}

		} catch (Exception $e) {
			echo implode(PHP_EOL, [
				'<span class="error">[Error]</span>',
				'<div class="error-message">'. $e->getMessage() .'</div></p>',
				'',
				'',
			]);
		}
	}
