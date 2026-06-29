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
		"select * from ". DB_TABLE_PREFIX ."settings_groups
		order by priority, `key`;"
	)->each(function($group) use (&$app_config) {

		$group['name'] = !empty($group['name']) ? json_decode($group['name'], true) : [];
		$group['name'] = $group['name'][language::$selected['code']] ?? $group['name']['en'] ?? '';

		$app_config['menu'][] = [
			'title' => $group['name'],
			'doc' => $group['key'],
			'params' => [],
		];

		$app_config['docs'][$group['key']] = 'settings.inc.php';
	});

	return $app_config;
