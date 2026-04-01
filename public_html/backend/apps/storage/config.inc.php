<?php

	return [
		'name' => t('title_storage', 'Storage'),
		'default' => 'storage',
		'group' => 'system',
		'priority' => 0,
		'theme' => [
			'color' => '#7ccdff',
			'icon' => 'icon-folder',
		],
		'menu' => [],
		'docs' => [
			'download' => 'download.inc.php',
			'edit_file' => 'edit_file.inc.php',
			'edit_folder' => 'edit_folder.inc.php',
			'storage' => 'storage.inc.php',
		],
	];
