<?php
	
	return [
		'name' => t('title_addons', 'Add-ons'),
		'default' => 'marketplace',
		'group' => 'system',
		'priority' => 0,
	
		'theme' => [
			'color' => '#4dcac3',
			'icon' => 'icon-newsstand',
		],
	
		'menu' => [
			[
				'title' => t('title_marketplace', 'Marketplace'),
				'doc' => 'marketplace',
				'params' => [],
			],
			[
				'title' => t('title_installed', 'Installed'),
				'doc' => 'installed',
				'params' => [],
			],
			[
				'title' => t('title_licenses', 'Licenses'),
				'doc' => 'licenses',
				'params' => [],
			],
		],
	
		'docs' => [
			'addon' => 'addon.inc.php',
			'catalog' => 'catalog.inc.php',
			'connect' => 'connect.inc.php',
			'disconnect' => 'disconnect.inc.php',
			'download' => 'download.inc.php',
			'edit_addon' => 'edit_addon.inc.php',
			'installed' => 'installed.inc.php',
			'marketplace' => 'marketplace.inc.php',
			'marketplace_addon' => 'marketplace_addon.inc.php',
			'licenses' => 'licenses.inc.php',
			'sources' => 'sources.inc.php',
		],
	];
