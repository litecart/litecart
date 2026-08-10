<?php

	breadcrumbs::add(t('title_dashboard', 'Dashboard'), document::ilink(''));

	// Display app content
	if (defined('__APP__')) {

		// Get app config
		$app_config = require 'app://backend/apps/'. __APP__ .'/config.inc.php';

		// Set default document if missing
		if (!defined('__DOC__')) {
			define('__DOC__', $app_config['default']);
		}

		$app_config['theme'] = [
			'icon' => $app_config['theme']['icon'] ?? 'icon-plus',
			'color' => $app_config['theme']['color'] ?? '#97a3b5',
		];

		// Check if administrator is permitted to access document.
		// Allow helper endpoints implicitly as long as the admin has the app enabled.
		if (!empty(administrator::$data['permissions']['apps'][__APP__])) {
			if (preg_match('/\.(json|csv)$/', __DOC__) || str_ends_with(__DOC__, '_picker')) {
				if (!in_array(__DOC__, administrator::$data['permissions']['apps'][__APP__])) {
					notices::add('errors', t('title_access_denied', 'Access Denied'));
					return;
				}
			}
		}

		// Resolve requested document file
		$doc_file = $app_config['docs'][__DOC__] ?? null;
		if (!$doc_file || !file_exists('app://backend/apps/'. __APP__ .'/'. $doc_file)) {
			notices::add('errors', __APP__ .'/'. f::escape_html(__DOC__) . ' is not a valid app document');
			return;
		}

		// Render the app document
		$_content = new ent_view('app://backend/apps/'. __APP__ .'/'. $doc_file);

		$_content->snippets = [
			'app_icon' => implode(PHP_EOL, [
				'<span class="app-icon">',
				'	' . f::draw_fonticon($app_config['theme']['icon']),
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

		document::$title[] = t('title_dashboard', 'Dashboard');

		if (file_exists(FS_DIR_APP . 'install/')) {
			notices::add('warnings', t('warning_install_folder_exists', 'Warning: The installation directory is still available and should be deleted.'), 'install_folder');
		}

		if (settings::get('maintenance_mode')) {
			notices::add('notices', t('reminder_store_in_maintenance_mode', 'The store is in maintenance mode.'));
		}

		// Widgets

		$box_widgets = new ent_view('app://backend/template/partials/box_widgets.inc.php');
		$box_widgets->snippets['widgets'] = [];

		$widgets = f::admin_get_widgets();

		foreach ($widgets as $widget) {
			if (!empty(administrator::$data['permissions']['widgets']) && !in_array($widget['id'], administrator::$data['permissions']['widgets'])) continue;

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
