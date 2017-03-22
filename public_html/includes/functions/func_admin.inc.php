<?php

  function admin_get_apps() {

    $apps_cache_id = cache::cache_id('admin_apps', array('language'));
    if (!$apps = cache::get($apps_cache_id, 'file')) {
      $apps = array();

      foreach (glob('*.app/') as $dir) {
        $code = rtrim($dir, '.app/');
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $dir . 'config.inc.php');
        $apps[$code] = array_merge(array('code' => $code, 'dir' => $dir), $app_config);
      }

      usort($apps, function($a, $b) use ($apps) {
        return ($a['name'] < $b['name']) ? -1 : 1;
      });

      cache::set($apps_cache_id, 'file', $apps);
    }

    return $apps;
  }

  function admin_get_widgets() {

    $widgets_cache_id = cache::cache_id('admin_widgets', array('language'));
    if (!$widgets = cache::get($widgets_cache_id, 'file')) {
      $widgets = array();

      foreach (glob('*.widget/') as $dir) {
        $code = rtrim($dir, '.widget/');
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $dir . 'config.inc.php');
        $widgets[$code] = array_merge(array('code' => $code, 'dir' => $dir), $widget_config);
      }

      usort($widgets, function($a, $b) use ($widgets) {
        //return ($a['name'] < $b['name']) ? -1 : 1;
        return ($a['priority'] < $b['priority']) ? -1 : 1;
      });

      cache::set($widgets_cache_id, 'file', $widgets);
    }

    return $widgets;
  }
