<?php

	return [
		'name' => t('title_banners', 'Banners'),
		'group' => 'website',
		'default' => 'banners',
		'priority' => 0,

		'theme' => [
			'color' => '#4093b3',
			'icon' => 'icon-banner',
		],

		'menu' => [],

		'docs' => [
			'banners' => 'banners.inc.php',
			'edit_banner' => 'edit_banner.inc.php',
		],
	];
