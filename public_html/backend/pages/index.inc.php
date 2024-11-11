<?php

	breadcrumbs::reset();
	breadcrumbs::add(language::translate('title_dashboard', 'Dashboard'), document::ilink(''));

	// Display app content
	if (defined('__APP__')) {

		// Get app config
		$app_config = require 'app://backend/apps/'. __APP__ .'/config.inc.php';

		// Set default document if missing
		if (!defined('__DOC__')) {
			define('__DOC__', $app_config['default']);
		}

		$app_config['theme'] = [
			'icon' => fallback($app_config['theme']['icon'], 'icon-plus'),
			'color' => fallback($app_config['theme']['color'], '#97a3b5'),
		];

		// Check if administrator is permitted to access document
		if (!empty(administrator::$data['apps'][__APP__]['status']) && !in_array(__DOC__, administrator::$data['apps'][__APP__]['docs'])) {
			notices::add('errors', language::translate('title_access_denied', 'Access Denied'));
			return;
		}

		// Make sure document exists
		if (!file_exists('app://backend/apps/'. __APP__ .'/'. $app_config['docs'][__DOC__])) {
			notices::add('errors', __APP__ .'/'. functions::escape_html(__DOC__) . ' is not a valid app document');
			return;
		}

		breadcrumbs::add($app_config['name'], document::ilink(__APP__ .'/'. $app_config['default']));

		// Render the app document
		$_content = new ent_view('app://backend/apps/'. __APP__ .'/'. $app_config['docs'][__DOC__]);

		$_content->snippets = [
			'app_icon' => implode(PHP_EOL, [
				'<span class="app-icon">',
				'	' . functions::draw_fonticon($app_config['theme']['icon']),
				'</span>',
			]),
		];

		// Render the page
		$_page = new ent_view('app://backend/template/pages/doc.inc.php');

		$_page->snippets = [
			'app' => __APP__,
			'doc' => __DOC__,
			'theme' => [
				'icon' => $app_config['theme']['icon'],
				'color' => $app_config['theme']['color'],
			],
			'content' => (string)$_content,
		];

		echo $_page;

	// Display the start page
	} else {

		document::$title[] = language::translate('title_dashboard', 'Dashboard');

		if (file_exists(FS_DIR_APP . 'install/')) {
			notices::add('warnings', language::translate('warning_install_folder_exists', 'Warning: The installation directory is still available and should be deleted.'), 'install_folder');
		}

		if (settings::get('maintenance_mode')) {
			notices::add('notices', language::translate('reminder_store_in_maintenance_mode', 'The store is in maintenance mode.'));
		}

		// Widgets

		$box_widgets = new ent_view('app://backend/template/partials/box_widgets.inc.php');
		$box_widgets->snippets['widgets'] = [];

		$widgets = functions::admin_get_widgets();

		foreach ($widgets as $widget) {
			if (!empty(administrator::$data['widgets']) && empty(administrator::$data['widgets'][$widget['id']])) continue;

			ob_start();
			include $widget['directory'] . $widget['file'];
			$output = ob_get_clean();

			$box_widgets->snippets['widgets'][] = [
				'id' => $widget['id'],
				'content' => $output,
			];
		}

		echo $box_widgets;
	}
