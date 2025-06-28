<?php

	return [
		'name' => t('title_pages', 'Pages'),
		'group' => 'website',
		'default' => 'pages',
		'priority' => 0,

		'theme' => [
			'color' => '#99a785',
			'icon' => 'icon-document',
		],

		'menu' => [
			[
				'title' => t('title_pages', 'Pages'),
				'doc' => 'pages',
				'params' => [],
			],
			[
				'title' => t('title_csv_import_export', 'CSV Import/Export'),
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
