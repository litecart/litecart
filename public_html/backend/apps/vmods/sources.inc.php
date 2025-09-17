<?php

	try {

		if (empty($_GET['pattern'])) {
			throw new Exception('Missing file');
		}

		$results = [];

		$skip_list = [
			'#.*(?<!\.inc\.php)$#',
			'#^assets/#',
			'#^index.php$#',
			'#^includes/app_header.inc.php$#',
			'#^includes/nodes/nod_vmod.inc.php$#',
			'#^includes/wrappers/wrap_app.inc.php$#',
			'#^includes/wrappers/wrap_storage.inc.php$#',
			'#^(assets|install|storage)/#',
			'#^(cache|data|ext|images|install|logs)/#',
		];

		$files = functions::file_search(FS_DIR_APP . $_GET['pattern'], GLOB_BRACE);

		foreach ($files as $file) {
			$relative_path = functions::file_relative_path($file);

			foreach ($skip_list as $pattern) {
				if (preg_match($pattern, $relative_path)) continue 2;
			}

			$results[$relative_path] = file_get_contents($file);
		}

	} catch (Exception $e) {
		$results = [];
	}

	header('Content-Type: application/json');
	echo functions::json_format($results);
	exit;
