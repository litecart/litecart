<?php

  function admin_get_apps() {

    $apps_cache_token = cache::token('admin_apps', ['language']);
    if (!$apps = cache::get($apps_cache_token)) {

      $apps = [];

      foreach (glob(FS_DIR_APP . 'backend/apps/*', GLOB_ONLYDIR) as $directory) {
        if (!$app_config = require vmod::check($directory . '/config.inc.php')) continue;

        $code = basename($directory);
        $apps[$code] = array_merge(['code' => $code, 'directory' => rtrim($directory, '/') . '/'], $app_config);
      }

      usort($apps, function($a, $b) use ($apps) {

        if (!isset($a['priority'])) $a['priority'] = 0;
        if (!isset($b['priority'])) $b['priority'] = 0;

        if ($a['priority'] == $b['priority']) {
          return ($a['name'] < $b['name']) ? -1 : 1;
        }

        return ($a['priority'] < $b['priority']) ? -1 : 1;
      });

      cache::set($apps_cache_token, $apps);
    }

    return $apps;
  }

  function admin_get_widgets() {

    $widgets_cache_token = cache::token('admin_widgets', ['language']);
    if (!$widgets = cache::get($widgets_cache_token)) {

      $widgets = [];

      foreach (glob(FS_DIR_APP . 'backend/widgets/*', GLOB_ONLYDIR) as $directory) {
        if (!$widget_config = require vmod::check($directory . '/config.inc.php')) return;

        $code = basename($directory);
        $widgets[$code] = array_merge(['code' => $code, 'directory' => rtrim($directory, '/') . '/'], $widget_config);
      }

      usort($widgets, function($a, $b) use ($widgets) {

        if (!isset($a['priority'])) $a['priority'] = 0;
        if (!isset($b['priority'])) $b['priority'] = 0;

        if ($a['priority'] == $b['priority']) {
          return ($a['name'] < $b['name']) ? -1 : 1;
        }

        return ($a['priority'] < $b['priority']) ? -1 : 1;
      });

      cache::set($widgets_cache_token, $widgets);
    }

    return $widgets;
  }
