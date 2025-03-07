<?php

	$box_apps_menu = new ent_view('app://backend/template/partials/box_apps_menu.inc.php');

	$groups = [
		'website' => [
			'id' => 'website',
			'name' => language::translate('title_website', 'Website'),
			'apps' => [],
		],
		'sales' => [
			'id' => 'sales',
			'name' => language::translate('title_sales', 'Sales'),
			'apps' => [],
		],
		'regional' => [
			'id' => 'regional',
			'name' => language::translate('title_regional', 'Regional'),
			'apps' => [],
		],
		'system' => [
			'id' => 'system',
			'name' => language::translate('title_system', 'System'),
			'apps' => [],
		],
		'other' => [
			'id' => 'other',
			'name' => language::translate('title_other', 'Other'),
			'apps' => [],
		],
		'addons' => [
			'id' => 'addons',
			'name' => language::translate('title_addons', 'Addons'),
			'apps' => [],
		],
	];

	$apps = functions::admin_get_apps();

	foreach ($apps as $app) {

		if (empty($app['group'])) {
			$app['group'] = 'other';
		}

		if (!empty(administrator::$data['apps']) && empty(administrator::$data['apps'][$app['id']]['status'])) continue;

		$app_item = [
			'id' => $app['id'],
			'name' => $app['name'],
			'link' => document::ilink($app['id'] .'/'. $app['default']),
			'theme' => [
				'icon' => !(empty($app['theme']['icon'])) ? $app['theme']['icon'] : 'icon-plus',
				'color' => !(empty($app['theme']['color'])) ? $app['theme']['color'] : '#97a3b5',
			],
			'active' => (defined('__APP__') && __APP__ == $app['id']),
			'menu' => [],
		];

		if (!empty($app['menu'])) {
			foreach ($app['menu'] as $menu_item) {

				if (!empty(administrator::$data['apps']) && (empty(administrator::$data['apps'][$app['id']]['status']) || !in_array($menu_item['doc'], administrator::$data['apps'][$app['id']]['docs']))) continue;

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
				} else {
					$selected = false;
				}

				$app_item['menu'][] = [
					'title' => $menu_item['title'],
					'doc' => $menu_item['doc'],
					'link' => document::ilink($app['id'] .'/'. $menu_item['doc'], fallback($menu_item['params'], [])),
					'active' => $selected,
				];
			}
		}

		$groups[$app['group']]['apps'][] = $app_item;
	}

	$groups = array_filter($groups, function($group) {
		return !empty($group['apps']);
	});

	$box_apps_menu->snippets['groups'] = $groups;

	echo $box_apps_menu;
