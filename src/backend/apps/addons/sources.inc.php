<?php

	try {

		if (empty($_GET['pattern'])) {
			throw new Exception('Missing file pattern');
		}

		$results = [];

		$skip_list = [
			'#.*(?<!\.inc\.php)$#',
			'#^assets/#',
			'#^index.php$#',
			'#^shared/app_header.inc.php$#',
			'#^shared/nodes/nod_vmod.inc.php$#',
			'#^shared/wrappers/wrap_app.inc.php$#',
			'#^shared/wrappers/wrap_storage.inc.php$#',
			'#^install/#',
			'#^storage/#',
		];

		$files = f::file_search(FS_DIR_APP . $_GET['pattern'], GLOB_BRACE);

		foreach ($files as $file) {
			$relative_path = f::file_relative_path($file);

			foreach ($skip_list as $pattern) {
				if (preg_match($pattern, $relative_path)) {
					continue 2;
				}
			}

			$results[f::file_relative_path($file)] = file_get_contents($file);
		}

	} catch (Exception $e) {
		$results = [];
	}

	header('Content-Type: application/json; charset='. mb_http_output());
	echo f::format_json($results);
	exit;
