<?php

	return [
		'name' => t('title_countries', 'Countries'),
		'group' => 'regional',
		'default' => 'countries',
		'priority' => 0,

		'theme' => [
			'color' => '#21a9d2',
			'icon' => 'icon-flag',
		],

		'menu' => [],

		'docs' => [
			'countries' => 'countries.inc.php',
			'countries.json' => 'countries.json.inc.php',
			'edit_country' => 'edit_country.inc.php',
			'zones.json' => 'zones.json.inc.php',
		],
	];
