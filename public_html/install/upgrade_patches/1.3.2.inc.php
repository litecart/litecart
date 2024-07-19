<?php

	perform_action('modify', [
		FS_DIR_APP . 'includes/config.inc.php' => [
			[
				'search'  => "// Set session lifetime in seconds" . PHP_EOL,
				'replace' => "",
			],
			[
				'search'  => "  ini_set('session.gc_maxlifetime', 180000);" . PHP_EOL,
				'replace' => "",
			],
			[
				'search'  => "// Session Platform ID" . PHP_EOL,
				'replace' => "",
			],
			[
				'search'  => "  define('SESSION_UNIQUE_ID', 'litecart');" . PHP_EOL,
				'replace' => "",
			],
		],
	], 'abort');
