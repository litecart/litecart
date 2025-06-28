<?php

	return [
		'name' => t('title_vMods', 'vMods').'™',
		'default' => 'vmods',
		'group' => 'system',
		'priority' => 0,

		'theme' => [
			'color' => '#4dcac3',
			'icon' => 'icon-power-plug',
		],

		'menu' => [],

		'docs' => [
			'configure' => 'configure.inc.php',
			'edit_vmod' => 'edit_vmod.inc.php',
			'download' => 'download.inc.php',
			'sources' => 'sources.inc.php',
			'test' => 'test.inc.php',
			'view' => 'view.inc.php',
			'vmods' => 'vmods.inc.php',
		],
	];
