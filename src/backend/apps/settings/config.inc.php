<?php

	$app_config = [
		'name' => t('title_settings', 'Settings'),
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
		"select *, coalesce(json_value(name, '$.". database::input(language::$selected['code']) ."'), json_value(name, '$.en')) as name
		from ". DB_PREFIX ."settings_groups
		order by priority, `key`;"
	)->each(function($group) use (&$app_config) {

		$app_config['menu'][] = [
			'title' => $group['name'],
			'doc' => $group['key'],
			'params' => [],
		];

		$app_config['docs'][$group['key']] = 'settings.inc.php';
	});

	return $app_config;
