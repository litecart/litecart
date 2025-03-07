<?php

	$app_config = [
		'name' => language::translate('title_settings', 'Settings'),
		'group' => 'system',
		'default' => 'store_info',
		'priority' => 0,

		'theme' => [
			'color' => '#757575',
			'icon' => 'icon-cogs',
		],

		'menu' => [],
		'docs' => [],
	];

	database::query(
		"select * from ". DB_TABLE_PREFIX ."settings_groups
		order by priority, `key`;"
	)->each(function($group) use (&$app_config) {

		$app_config['menu'][] = [
			'title' => language::translate('settings_group:title_'.$group['key'], $group['name']),
			'doc' => $group['key'],
			'params' => [],
		];

		$app_config['docs'][$group['key']] = 'settings.inc.php';
	});

	return $app_config;
