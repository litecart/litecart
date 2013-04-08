<?php
  
  function admin_get_apps() {
    global $system;
    
    $apps_cache_id = $system->cache->cache_id('admin_apps', array('language'));
    if (!$apps = $system->cache->get($apps_cache_id, 'file')) {
      $apps = array();
      
      foreach (glob('*.app/') as $dir) {
        $code = rtrim($dir, '.app/');
        require(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $dir . 'config.inc.php');
        $apps[$code] = array_merge(array('code' => $code, 'dir' => $dir), $app_config);
      }
      
      usort($apps, function($a, $b) use ($apps) {
        return ($a['name'] < $b['name']) ? -1 : 1;
      });
      
      $system->cache->set($apps_cache_id, 'file', $apps);
    }
    
    return $apps;
  }
  
  function admin_get_widgets() {
    global $system;
    
    $widgets_cache_id = $system->cache->cache_id('admin_widgets', array('language'));
    if (!$widgets = $system->cache->get($widgets_cache_id, 'file')) {
      $widgets = array();
      
      foreach (glob('*.widget/') as $dir) {
        $code = rtrim($dir, '.widget/');
        require(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $dir . 'config.inc.php');
        $widgets[$code] = array_merge(array('code' => $code, 'dir' => $dir), $widget_config);
      }
      
      usort($widgets, function($a, $b) use ($widgets) {
        //return ($a['name'] < $b['name']) ? -1 : 1;
        return ($a['priority'] < $b['priority']) ? -1 : 1;
      });
      
      $system->cache->set($widgets_cache_id, 'file', $widgets);
    }
    
    return $widgets;
  }

?>