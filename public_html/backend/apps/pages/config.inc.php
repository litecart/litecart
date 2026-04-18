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
		],

		'docs' => [
			'pages' => 'pages.inc.php',
			'edit_page' => 'edit_page.inc.php',
		],
	];
