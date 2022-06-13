<?php

  function admin_get_apps() {

    $apps_cache_token = cache::token('backend_apps', ['language']);
    if (!$apps = cache::get($apps_cache_token)) {

      $apps = [];

      foreach (functions::file_search('app://backend/apps/*', GLOB_ONLYDIR) as $directory) {
        if (!$app_config = require $directory . '/config.inc.php') continue;

        $id = basename($directory);
        $apps[$id] = array_merge(['id' => $id, 'directory' => rtrim($directory, '/') . '/'], $app_config);
      }

      uasort($apps, function($a, $b) use ($apps) {

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

    $widgets_cache_token = cache::token('backend_widgets', ['language']);
    if (!$widgets = cache::get($widgets_cache_token)) {

      $widgets = [];

      foreach (functions::file_search('app://backend/widgets/*', GLOB_ONLYDIR) as $directory) {
        if (!$widget_config = require $directory . '/config.inc.php') return;

        $id = basename($directory);
        $widgets[$id] = array_merge(['id' => $id, 'directory' => rtrim($directory, '/') . '/'], $widget_config);
      }

      uasort($widgets, function($a, $b) use ($widgets) {

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
