<?php
  require_once('../includes/app_header.inc.php');

  user::require_login();

  document::$template = settings::get('store_template_admin');

  breadcrumbs::add(language::translate('title_admin_panel', 'Admin Panel'), WS_DIR_ADMIN);

// Build apps list menu
    $box_apps_menu = new view();
    $box_apps_menu->snippets['apps'] = array();

    foreach (functions::admin_get_apps() as $app) {
      $box_apps_menu->snippets['apps'][$app['code']] = array(
        'code' => $app['code'],
        'name' => $app['name'],
        'link' => document::link(WS_DIR_ADMIN, array('app' => $app['code'], 'doc' => $app['default'])),
        'theme' => array(
          'icon' => !(empty($app['theme']['icon'])) ? $app['theme']['icon'] : 'fa-plus',
          'color' => !(empty($app['theme']['color'])) ? $app['theme']['color'] : '#97a3b5',
        ),
        'active' => (isset($_GET['app']) && $_GET['app'] == $app['code']) ? true : false,
        'menu' => array(),
      );

      if (!empty($app['menu'])) {
        foreach ($app['menu'] as $item) {

          $params = !empty($item['params']) ? array_merge(array('app' => $app['code'], 'doc' => $item['doc']), $item['params']) : array('app' => $app['code'], 'doc' => $item['doc']);

          if (isset($_GET['doc']) && $_GET['doc'] == $item['doc']) {
            $selected = true;
            if (!empty($item['params'])) {
              foreach ($item['params'] as $param => $value) {
                if (!isset($_GET[$param]) || $_GET[$param] != $value) {
                  $selected = false;
                  break;
                }
              }
            }
          } else {
            $selected = false;
          }

          $box_apps_menu->snippets['apps'][$app['code']]['menu'][] = array(
            'title' => $item['title'],
            'doc' => $item['doc'],
            'link' => document::link(WS_DIR_ADMIN, array('app' => $app['code'], 'doc' => $item['doc']) + (!empty($item['params']) ? $item['params'] : array())),
            'active' => $selected ? true : false,
          );
        }
      }
    }

    document::$snippets['box_apps_menu'] = $box_apps_menu->stitch('views/box_apps_menu');

  // Start page
    if (empty($_GET['app'])) {

    // Throw some warnings
      if (empty($_SERVER['REDIRECT_REMOTE_USER']) && empty($_SERVER['REMOTE_USER'])) {
        notices::add('warnings', language::translate('warning_admin_folder_not_protected', 'Warning: Your admin folder is not .htaccess protected'), 'unprotected');
      }

      if (file_exists(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'install/')) {
        notices::add('warnings', language::translate('warning_install_folder_exists', 'Warning: The installation directory is still available and should be deleted.'), 'install_folder');
      }

    // Widgets
      $box_widgets = new view();
      $box_widgets->snippets['widgets'] = array();

      foreach (functions::admin_get_widgets() as $widget) {
        ob_start();
        include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $widget['dir'] . $widget['file']);

        $box_widgets->snippets['widgets'][] = array(
          'code' => basename($widget['dir'], '.widget'),
          'content' => ob_get_clean(),
        );
      }

      echo $box_widgets->stitch('views/box_widgets');

  // App content
    } else {

      require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/config.inc.php');

      if (empty($app_config['theme']['icon']) && !empty($app_config['icon'])) $app_config['theme']['icon'] = $app_config['icon']; // Backwards compatibility

      breadcrumbs::add($app_config['name'], $app_config['default']);

      $_page = new view();
      $_page->snippets = array(
        'app' => $_GET['app'],
        'doc' => !empty($_GET['doc']) ? $_GET['doc'] : $app_config['default'],
        'theme' => array(
          'icon' => !empty($app_config['theme']['icon']) ? $app_config['theme']['icon'] : 'fa-plus',
          'color' => !empty($app_config['theme']['color']) ? $app_config['theme']['color'] : '#97a3b5',
        ),
        'help_link' => document::link('http://wiki.litecart.net/', array('id' => 'Admin:'. $_GET['app'] . (!empty($_GET['doc']) ? '/' . $_GET['doc'] : ''))),
      );

      $app_icon = '<span class="fa-stack icon-wrapper">' . PHP_EOL
                . '  ' . functions::draw_fonticon('fa-circle fa-stack-2x icon-background', 'style="color: '. $_page->snippets['theme']['color'] .';"') . PHP_EOL
                . '  ' . functions::draw_fonticon($_page->snippets['theme']['icon'] .' fa-stack-1x icon', 'style="color: #fff;"') . PHP_EOL
                . '</span>';

      ob_start();
      if (!empty($_GET['doc'])) {
        if (empty($app_config['docs'][$_GET['doc']]) || !file_exists(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/' . $app_config['docs'][$_GET['doc']])) trigger_error($_GET['app'] .'.app/'. htmlspecialchars($_GET['doc']) . ' is not a valid admin document', E_USER_ERROR);
        include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/' . $app_config['docs'][$_GET['doc']]);
      } else {
        include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/' . $app_config['docs'][$app_config['default']]);
      }
      $_page->snippets['doc'] = ob_get_clean();


      if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        echo $_page->stitch('pages/doc');
      } else {
        echo $_page->snippets['doc'];
      }
    }

  require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>