<?php

	return [
		'name' => language::translate('title_pages', 'Pages'),
		'default' => 'pages',
		'priority' => 0,

		'theme' => [
			'color' => '#99a785',
			'icon' => 'icon-file-text',
		],

		'menu' => [
			[
				'title' => language::translate('title_pages', 'Pages'),
				'doc' => 'pages',
				'params' => [],
			],
			[
				'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
				'doc' => 'csv',
				'params' => [],
			],
		],

		'docs' => [
			'pages' => 'pages.inc.php',
			'edit_page' => 'edit_page.inc.php',
			'csv' => 'csv.inc.php',
		],
	];
