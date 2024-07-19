<?php

	return [
		'name' => language::translate('title_geo_zones', 'Geo Zones'),
		'default' => 'geo_zones',
		'priority' => 0,

		'theme' => [
			'color' => '#3090e8',
			'icon' => 'fa-globe',
		],

		'menu' => [],

		'docs' => [
			'geo_zones' => 'geo_zones.inc.php',
			'edit_geo_zone' => 'edit_geo_zone.inc.php',
		],
	];
