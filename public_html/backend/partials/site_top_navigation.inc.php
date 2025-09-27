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
				'title' => t('title_webmail', 'Webmail'),
				'link' => ($webmail_link = settings::get('webmail_link')) ? functions::escape_html($webmail_link) : document::ilink('settings/advanced', ['key' => 'webmail_link', 'action' => 'edit']),
				'icon' => 'icon-envelope',
				'target' => '_blank',
				'icon_only' => true,
			],
			[
				'title' => t('title_control_panel', 'Control Panel'),
				'link' => ($control_panel_link = settings::get('control_panel_link')) ? functions::escape_html($control_panel_link) : document::ilink('settings/advanced', ['key' => 'control_panel_link', 'action' => 'edit']),
				'icon' => 'icon-cogs',
				'target' => '_blank',
				'icon_only' => true,
			],
			[
				'title' => t('title_database_manager', 'Database Manager'),
				'link' => ($database_admin_link = settings::get('database_admin_link')) ? functions::escape_html($database_admin_link) : document::ilink('settings/advanced', ['key' => 'database_admin_link', 'action' => 'edit']),
				'icon' => 'icon-database',
				'target' => '_blank',
				'icon_only' => true,
			],
			[
				'title' => t('title_frontend', 'Frontend'),
				'link' => document::ilink('f:'),
				'icon' => 'icon-display',
			],
			[
				'title' => t('title_help', 'Help'),
				'link' => 'https://litecart.net/wiki/',
				'icon' => 'icon-question',
				'target' => '_blank',
			],
			[
				'title' => t('title_sign_out', 'Sign Out'),
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

	if (is_file('app://backend/template/partials/site_top_navigation.inc.php')) {
		echo $_partial->render();
		return;
	} else {
		extract($_partial->snippets);
	}

?>
<style>
.brightness .form-toggle {
	padding: 0 !important;
	gap: 0;
}
</style>

<ul id="toolbar" class="shadow hidden-print">
	<li>
		<div>
			<label class="nav-toggle btn btn-default" for="sidebar-compact">
				<?php echo functions::draw_fonticon('icon-sidebar', 'style="font-size: 1.5em;"'); ?>
			</label>
		</div>
	</li>

	<li style="flex-grow: 1;">
		<div id="search" class="dropdown">
			<?php echo functions::form_input_search('query', false, 'placeholder="'. functions::escape_attr(t('title_search_entire_platform', 'Search entire platform')) .'&hellip;" autocomplete="off"'); ?>
			<div class="results dropdown-menu"></div>
		</div>
	</li>

	<li>
		<div class="btn-group" data-toggle="buttons">
			<button name="font_size" class="btn btn-default btn-sm" type="button" value="decrease"><span style="font-size: .8em;">A</span></button>
			<button name="font_size" class="btn btn-default btn-sm" type="button" value="increase"><span style="font-size: 1.25em;">A</span></button>
		</div>
	</li>

	<li class="brightness">
		<?php echo functions::form_toggle('dark_mode', ['0' => functions::draw_fonticon('icon-sun'), '1' => functions::draw_fonticon('icon-moon')]); ?>
	</li>

	<?php foreach ($items as $item) echo $draw_menu_item($item); ?>

</ul>