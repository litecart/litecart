<?php

  $box_apps_menu = new ent_view('views/box_apps_menu.inc.php');
  $box_apps_menu->snippets['apps'] = [];

  $apps_cache_token = cache::token('backend_apps', ['language']);
  if (!$apps = cache::get($apps_cache_token)) {
    $apps = functions::admin_get_apps();
    cache::set($apps_cache_token, $apps);
  }

  foreach ($apps as $app) {

    if (!empty(user::$data['apps']) && empty(user::$data['apps'][$app['code']]['status'])) continue;

    $box_apps_menu->snippets['apps'][$app['code']] = [
      'code' => $app['code'],
      'name' => $app['name'],
      'link' => document::ilink($app['code'] .'/'. $app['default']),
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
          'link' => document::ilink($app['code'] .'/'. $item['doc'], !empty($item['params']) ? $item['params'] : []),
          'active' => $selected ? true : false,
        ];
      }
    }
  }
