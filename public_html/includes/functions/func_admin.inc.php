<?php

  function admin_get_apps() {

    $apps_cache_token = cache::token('admin_apps', array('language'), 'file');
    if (!$apps = cache::get($apps_cache_token)) {
      $apps = array();

      foreach (glob('*.app/') as $dir) {
        $code = rtrim($dir, '.app/');
        $app_config = require vmod::check(FS_DIR_ADMIN . $dir . 'config.inc.php');
        if (!is_array($app_config)) require vmod::check(FS_DIR_ADMIN . $dir . 'config.inc.php'); // Backwards compatibility
        $apps[$code] = array_merge(array('code' => $code, 'dir' => $dir), $app_config);
      }

      usort($apps, function($a, $b) use ($apps) {
        if (@$a['priority'] == @$b['priority']) {
          return ($a['name'] < $b['name']) ? -1 : 1;
        }
        return (@$a['priority'] < @$b['priority']) ? -1 : 1;
      });

      cache::set($apps_cache_token, $apps);
    }

    return $apps;
  }

  function admin_get_widgets() {

    $widgets_cache_token = cache::token('admin_widgets', array('language'), 'file');
    if (!$widgets = cache::get($widgets_cache_token)) {
      $widgets = array();

      foreach (glob('*.widget/') as $dir) {
        $code = rtrim($dir, '.widget/');
        $widget_config = require vmod::check(FS_DIR_ADMIN . $dir . 'config.inc.php');
        if (!is_array($widget_config)) require vmod::check(FS_DIR_ADMIN . $dir . 'config.inc.php'); // Backwards compatibility
        $widgets[$code] = array_merge(array('code' => $code, 'dir' => $dir), $widget_config);
      }

      usort($widgets, function($a, $b) use ($widgets) {
        if ($a['priority'] == $b['priority']) {
          return ($a['name'] < $b['name']) ? -1 : 1;
        }
        return ($a['priority'] < $b['priority']) ? -1 : 1;
      });

      cache::set($widgets_cache_token, $widgets);
    }

    return $widgets;
  }
