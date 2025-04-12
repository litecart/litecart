<?php

	$_partial = new ent_view('app://backend/template/partials/site_top_navigation.inc.php');

	$_partial->snippets = [
		'items' => [
			[
				'title' => language::$selected['code'],
				'link' => '#',
				'icon' => 'icon-search',
				'subitems' => array_map(function($language) {
					return [
						'title' => $language['name'],
						'link' => document::ilink(null, [], [], [], $language['code']),
					];
				}, language::$languages),
			],
			[
				'title' => language::translate('title_webmail', 'Webmail'),
				'link' => ($webmail_link = settings::get('webmail_link')) ? functions::escape_html($webmail_link) : document::ilink('settings/advanced', ['key' => 'webmail_link', 'action' => 'edit']),
				'icon' => 'icon-envelope',
				'target' => '_blank',
				'icon_only' => true,
			],
			[
				'title' => language::translate('title_control_panel', 'Control Panel'),
				'link' => ($control_panel_link = settings::get('control_panel_link')) ? functions::escape_html($control_panel_link) : document::ilink('settings/advanced', ['key' => 'control_panel_link', 'action' => 'edit']),
				'icon' => 'icon-cogs',
				'target' => '_blank',
				'icon_only' => true,
			],
			[
				'title' => language::translate('title_database_manager', 'Database Manager'),
				'link' => ($database_admin_link = settings::get('database_admin_link')) ? functions::escape_html($database_admin_link) : document::ilink('settings/advanced', ['key' => 'database_admin_link', 'action' => 'edit']),
				'icon' => 'icon-database',
				'target' => '_blank',
				'icon_only' => true,
			],
			[
				'title' => language::translate('title_frontend', 'Frontend'),
				'link' => document::ilink('f:'),
				'icon' => 'icon-display',
			],
			[
				'title' => language::translate('title_help', 'Help'),
				'link' => 'https://litecart.net/wiki/',
				'icon' => 'icon-question',
				'target' => '_blank',
			],
			[
				'title' => language::translate('title_sign_out', 'Sign Out'),
				'link' => document::ilink('logout'),
				'icon' => 'icon-sign-out',
			],
		],
	];

	$draw_menu_item = function($item, $indent = 0, $is_dropdown_item=false) use (&$draw_menu_item) {

		if (!empty($item['subitems'])) {
			return implode(PHP_EOL, [
				'<li class="'. ($is_dropdown_item ? 'dropdown-item' : 'nav-item') .' dropdown'. (!empty($item['hidden-xs']) ? ' hidden-xs' : '') .'"'. (!empty($item['id']) ? ' data-id="'. functions::escape_attr($item['id']) .'"' : '') .'>',
				'	<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">',
				'		'. $item['title'],
				!empty($item['badge']) ? '		<div class="badge">'. $item['badge'] .'</div>' : '',
				'	</a>',
				'	<ul class="dropdown-menu">',
				'		'. implode(PHP_EOL, array_map(function($subitem) use ($draw_menu_item, $indent) {
					return $draw_menu_item($subitem, $indent+1, true);
				}, $item['subitems'])),
				'	</ul>',
				'</li>',
			]);
		}

		return implode(PHP_EOL, [
			'<li class="'. ($is_dropdown_item ? 'dropdown-item' : 'nav-item') . (!empty($item['hidden-xs']) ? ' hidden-xs' : '') .'"'. (!empty($item['id']) ? ' data-id="'. functions::escape_attr($item['id']) .'"' : '') .'>',
			'	<a class="nav-link" href="'. functions::escape_attr($item['link']) .'" target="'. (!empty($item['target']) ? 'target="'. functions::escape_attr($item['target']) .'"' : '') .'">',
			'		'. (!empty($item['icon']) ? functions::draw_fonticon($item['icon']) : '') . (empty($item['icon_only']) ? ' '. functions::escape_html($item['title']) : ''),
			!empty($item['badge']) ? '		<div class="badge">'. $item['badge'] .'</div>' : '',
			'	</a>',
			'</li>',
		]);
	};

	$_partial->snippets['draw_menu_item'] = $draw_menu_item;

	echo $_partial->render();
