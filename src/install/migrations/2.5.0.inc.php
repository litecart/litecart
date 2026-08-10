<?php

	// Delete some files
	perform_action('delete', [
		FS_DIR_ADMIN . '.htaccess',
		FS_DIR_ADMIN . '.htpasswd',
		FS_DIR_ADMIN . 'catalog.app/edit_option_group.inc.php',
		FS_DIR_ADMIN . 'catalog.app/edit_product_group.inc.php',
		FS_DIR_ADMIN . 'customers.app/newsletter.inc.php',
		FS_DIR_ADMIN . 'vqmods.app/',
		FS_DIR_APP . 'ext/jquery/jquery-3.6.0.min.js',
		FS_DIR_APP . 'vqmod/xml/product_options_stock_notice.xml',
	]);

	// Move vQmods to vMod
	foreach (glob(FS_DIR_APP . 'vqmod/xml/*.{xml,disabled}', GLOB_BRACE) as $file) {
		perform_action('move', [$file => FS_DIR_STORAGE . 'vmods/' . basename($file)]);
	}

	perform_action('delete', [
		FS_DIR_APP . 'vqmod/',
	]);

	// Modify some files
	perform_action('modify', [
		FS_DIR_APP . '.htaccess' => [
			[
				'search'  => "RewriteRule ^.*$ index.php?%{QUERY_STRING} [L]",
				'replace' => "RewriteRule ^.*$ index.php [QSA,L]",
				'regex'   => false,
			]
		],
		FS_DIR_APP . 'includes/config.inc.php' => [
			[
				'search'  => "  define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');",
				'replace' => implode(PHP_EOL, [
					"  define('FS_DIR_STORAGE',     FS_DIR_APP);",
					"  define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');",
				]),
				'regex'   => false,
			],
			[
				'search'  => "  define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');",
				'replace' => implode(PHP_EOL, [
					"  define('WS_DIR_STORAGE',     WS_DIR_APP);",
					"  define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');",
				]),
				'regex'   => false,
			],
			[
				'search'  => implode(PHP_EOL, [
					"## Backwards Compatible Directory Definitions (LiteCart <2.2)  #######",
					"######################################################################",
					".*?",
					"######################################################################",
				]),
				'replace' => '',
				'regex'   => true,
			],
			[
				'search'  => "#  define\('DB_PERSISTENT_CONNECTIONS', '[^']+'\);(\r\n?|\n)?#",
				'replace' => '',
				'regex'   => true,
			],
			[
				'search'  => "ini_set('error_log', FS_DIR_APP . 'logs/errors.log');",
				'replace' => "ini_set('error_log', FS_DIR_STORAGE . 'logs/errors.log');",
				'regex'   => false,
			],
			[
				'search'  => implode(PHP_EOL, [
					"#// Password Encryption Salt",
					"  define('PASSWORD_SALT', '[^']+');",
				]),
				'replace' => '',
				'regex'   => true,
			],
			[
				'search'  => '#\s*\?>\s*$#',
				'replace' => PHP_EOL,
				'regex'   => true,
			],
		],
	]);

	// Copy some files
	perform_action('copy', [
		FS_DIR_APP . 'install/data/default/src/images/favicons/' => FS_DIR_STORAGE . 'images/favicons/',
	]);
