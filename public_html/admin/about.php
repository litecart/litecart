<?php
  require_once('../includes/app_header.inc.php');

  user::require_login();

  document::$template = settings::get('store_template_admin');

  breadcrumbs::reset();
  breadcrumbs::add(language::translate('title_dashboard', 'Dashboard'), WS_DIR_ADMIN);
  breadcrumbs::add(language::translate('title_about', 'About'));

// Build apps list menu
  $box_apps_menu = new ent_view();
  $box_apps_menu->snippets['apps'] = [];

  foreach (functions::admin_get_apps() as $app) {

    if (!empty(user::$data['apps']) && empty(user::$data['apps'][$app['code']]['status'])) continue;

    $box_apps_menu->snippets['apps'][$app['code']] = [
      'code' => $app['code'],
      'name' => $app['name'],
      'link' => document::link(WS_DIR_ADMIN, ['app' => $app['code'], 'doc' => $app['default']]),
      'theme' => [
        'icon' => !(empty($app['theme']['icon'])) ? $app['theme']['icon'] : 'fa-plus',
        'color' => !(empty($app['theme']['color'])) ? $app['theme']['color'] : '#97a3b5',
      ],
      'active' => (isset($_GET['app']) && $_GET['app'] == $app['code']) ? true : false,
      'menu' => [],
    ];

    if (!empty($app['menu'])) {
      foreach ($app['menu'] as $item) {

        if (!empty(user::$data['apps']) && (empty(user::$data['apps'][$app['code']]['status']) || !in_array($item['doc'], user::$data['apps'][$app['code']]['docs']))) continue;

        $params = !empty($item['params']) ? array_merge(['app' => $app['code'], 'doc' => $item['doc']], $item['params']) : ['app' => $app['code'], 'doc' => $item['doc']];

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

        $box_apps_menu->snippets['apps'][$app['code']]['menu'][] = [
          'title' => $item['title'],
          'doc' => $item['doc'],
          'link' => document::link(WS_DIR_ADMIN, ['app' => $app['code'], 'doc' => $item['doc']] + (!empty($item['params']) ? $item['params'] : [])),
          'active' => $selected ? true : false,
        ];
      }
    }
  }

  document::$snippets['box_apps_menu'] = $box_apps_menu->stitch('views/box_apps_menu');

  include vmod::check(FS_DIR_TEMPLATE . 'pages/about.inc.php');

  require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
