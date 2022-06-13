<?php

  user::require_login();

  breadcrumbs::reset();
  breadcrumbs::add(language::translate('title_dashboard', 'Dashboard'), document::ilink(''));

// Apps
  include 'app://backend/partials/box_apps_menu.inc.php';
  document::$snippets['box_apps_menu'] = $box_apps_menu;

// Display app content
  if (defined('__APP__')) {

  // Get app config
    $app_config = require 'apps://backend/apps/'. __APP__ .'/config.inc.php';

  // Set default document if missing
    if (!defined('__DOC__')) define('__DOC__', $app_config['default']);

  // Check if user is permitted to access document
    if (!empty(user::$data['apps'][__APP__]['status']) && !in_array(__DOC__, user::$data['apps'][__APP__]['docs'])) {
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
      'app_icon' => '<span class="app-icon">' . PHP_EOL
                  . '  ' . functions::draw_fonticon($app_config['theme']['icon'] .' fa-fw') . PHP_EOL
                  . '</span>',
    ];

  // Render the page
    $_page = new ent_view(FS_DIR_TEMPLATE . 'pages/doc.inc.php');

    $_page->snippets = [
      'app' => __APP__,
      'doc' => __DOC__,
      'theme' => [
        'icon' => fallback($app_config['theme']['icon'], 'fa-plus'),
        'color' => fallback($app_config['theme']['color'], '#97a3b5'),
      ],
      'content' => (string)$_content,
    ];

    echo $_page;
    //if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    //  return;
    //}

// Display the start page
  } else {

    document::$snippets['title'][] = language::translate('title_dashboard', 'Dashboard');

    if (file_exists(FS_DIR_APP . 'install/')) {
      notices::add('warnings', language::translate('warning_install_folder_exists', 'Warning: The installation directory is still available and should be deleted.'), 'install_folder');
    }

    if (settings::get('maintenance_mode')) {
      notices::add('notices', language::translate('reminder_store_in_maintenance_mode', 'The store is in maintenance mode.'));
    }

  // Widgets

    $box_widgets = new ent_view(FS_DIR_TEMPLATE . 'partials/box_widgets.inc.php');
    $box_widgets->snippets['widgets'] = [];

    $widgets = functions::admin_get_widgets();

    foreach ($widgets as $widget) {
      if (!empty(user::$data['widgets']) && empty(user::$data['widgets'][$widget['id']])) continue;

      ob_start();
      include vmod::check($widget['directory'] . $widget['file']);

      $box_widgets->snippets['widgets'][] = [
        'id' => $widget['id'],
        'content' => ob_get_clean(),
      ];
    }

    echo $box_widgets;
  }
