<?php

	$sidebar = new ent_view('app://backend/template/partials/sidebar.inc.php');

	$groups = [
		'ecommerce' => [
			'id' => 'ecommerce',
			'name' => t('title_ecommerce', 'Ecommerce'),
			'apps' => [],
		],
		'website' => [
			'id' => 'website',
			'name' => t('title_website', 'Website'),
			'apps' => [],
		],
		'regional' => [
			'id' => 'regional',
			'name' => t('title_regional', 'Regional'),
			'apps' => [],
		],
		'system' => [
			'id' => 'system',
			'name' => t('title_system', 'System'),
			'apps' => [],
		],
		'tools' => [
			'id' => 'tools',
			'name' => t('title_tools', 'Tools'),
			'apps' => [],
		],
		'other' => [
			'id' => 'other',
			'name' => t('title_other', 'Other'),
			'apps' => [],
		],
	];

	$apps = f::admin_get_apps();

	foreach ($apps as $app) {

		if (empty($app['group'])) {
			$app['group'] = 'other';
		}

		if (!empty(administrator::$data['permissions']['apps'])) {
			if (empty(administrator::$data['permissions']['apps'][$app['id']])) continue;
		}

		$app_item = [
			'id' => $app['id'],
			'name' => $app['name'],
			'link' => document::ilink($app['id'] .'/'. $app['default']),
			'theme' => [
				'icon' => !(empty($app['theme']['icon'])) ? $app['theme']['icon'] : 'add',
				'color' => !(empty($app['theme']['color'])) ? $app['theme']['color'] : '#97a3b5',
			],
			'active' => (defined('__APP__') && __APP__ == $app['id']),
			'menu' => [],
		];

		if (!empty($app['menu'])) {
			foreach ($app['menu'] as $menu_item) {

				if (!empty(administrator::$data['permissions']['apps'][$app['id']]) && !in_array($menu_item['doc'], administrator::$data['permissions']['apps'][$app['id']])) {
					continue;
				}

				$selected = false;

				$params = !empty($menu_item['params']) ? array_merge(['app' => $app['id'], 'doc' => $menu_item['doc']], $menu_item['params']) : ['app' => $app['id'], 'doc' => $menu_item['doc']];

				if (defined('__DOC__') && __DOC__ == $menu_item['doc']) {
					$selected = true;
					if (!empty($menu_item['params'])) {
						foreach ($menu_item['params'] as $param => $value) {
							if (!isset($_GET[$param]) || $_GET[$param] != $value) {
								$selected = false;
								break;
							}
						}
					}
				}

				$app_item['menu'][] = [
					'title' => $menu_item['title'],
					'doc' => $menu_item['doc'],
					'link' => document::ilink($app['id'] .'/'. $menu_item['doc'], $menu_item['params'] ?? []),
					'active' => $selected,
				];
			}
		}

		$groups[$app['group']]['apps'][] = $app_item;
	}

	$groups = array_filter($groups, function($group) {
		return !empty($group['apps']);
	});

	$sidebar->snippets['groups'] = $groups;

	echo $sidebar;
