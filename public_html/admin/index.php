<?php
  require_once('../includes/app_header.inc.php');
  
  $system->document->template = $system->settings->get('store_template_admin');
  
  $system->breadcrumbs->add($system->language->translate('title_admin_panel', 'Admin Panel'), WS_DIR_ADMIN);
  
  if (empty($_SERVER['REDIRECT_REMOTE_USER']) && empty($_SERVER['REMOTE_USER'])) {
    $system->notices->add('warnings', $system->language->translate('warning_admin_folder_not_protected', 'Warning: Your admin folder is not .htaccess protected'), 'unprotected');
  }
  
// Read all apps
  $apps_cache_id = $system->cache->cache_id('admin_apps', array('language'));
  if (!$apps = $system->cache->get($apps_cache_id, 'file')) {
    $apps = array();
    
    foreach (glob('*.app/') as $dir) {
      $code = rtrim($dir, '.app/');
      require(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $dir . 'config.inc.php');
      $apps[$code] = array_merge(array('code' => $code, 'dir' => $dir), $app_config);
    }
    
    function sort_app_modules($a, $b) {
      return ($a['name'] < $b['name']) ? -1 : 1;
    }
    usort($apps, 'sort_app_modules');
    
    $system->cache->set($apps_cache_id, 'file', $apps);
  }
  
// Read all widgets
  $widgets_cache_id = $system->cache->cache_id('admin_widgets', array('language'));
  if (!$widgets = $system->cache->get($widgets_cache_id, 'file')) {
    $widgets = array();
    
    foreach (glob('*.widget/') as $dir) {
      $code = rtrim($dir, '.widget/');
      require(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $dir . 'config.inc.php');
      $widgets[$code] = array_merge(array('code' => $code, 'dir' => $dir), $widget_config);
    }
    
    function sort_widget_modules($a, $b) {
      return ($a['name'] < $b['name']) ? -1 : 1;
    }
    usort($widgets, 'sort_widget_modules');
    
    $system->cache->set($widgets_cache_id, 'file', $widgets);
  }
  
// Build apps list menu
  $sidebar = '<div id="apps-list-menu-wrapper">' . PHP_EOL
           . '  <ul>';
           
  foreach ($apps as $app) {
    $params = !empty($app['params']) ? array_merge(array('app' => $app['code'], 'doc' => $app['index']), $app['params']) : array('app' => $app['code'], 'doc' => $app['index']);
    $sidebar .= '    <li'. ((isset($_GET['app']) && $_GET['app'] == $app['code']) ? ' class="selected"' : '') .'>'. PHP_EOL .'      <a href="'. $system->document->link('', $params) .'"><img src="'. WS_DIR_ADMIN . $app['code'] .'.app/'. $app['icon'] .'" width="24" height="24" border="0" align="absmiddle" /> '. $app['name'] .'</a>' . PHP_EOL;
    
    if (!empty($_GET['app']) && $_GET['app'] == $app['code']) {
    
      if (!empty($app['menu'])) {
        $sidebar .= '      <ul>' . PHP_EOL;
        
        foreach ($app['menu'] as $item) {
          if (isset($_GET['doc']) && $_GET['doc'] == $item['link']) {
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
          
          $params = !empty($item['params']) ? array_merge(array('app' => $app['code'], 'doc' => $item['link']), $item['params']) : array('app' => $app['code'], 'doc' => $item['link']);
          $sidebar .= '        <li'. ($selected ? ' class="selected"' : '') .'><a href="'. $system->document->link(WS_DIR_ADMIN, $params) .'"> &bull;&nbsp; '. $item['name'] .'</a></li>' . PHP_EOL;
        }
        
        $sidebar .= '      </ul>' . PHP_EOL;
      }
    }
    
    $sidebar .= '    </li>' . PHP_EOL;
  }
  
  
  $sidebar .= '    <li><a href="'. str_replace('://', '://logout:logout@', $system->document->link(WS_DIR_ADMIN)) .'"><img src="'. WS_DIR_IMAGES .'icons/48x48/exit.png" width="24" height="24" border="0" align="absmiddle" /> '. $system->language->translate('title_logout', 'Logout') .'</a></li>' . PHP_EOL
            . '  </ul>' . PHP_EOL
            . '</div>';
  
  $system->document->snippets['sidebar_content'] = '<div id="apps-list-menu">'. $sidebar . '</div>';
  
// App content
  if (!empty($_GET['app'])) {
    
    require(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/config.inc.php');
    
    $system->document->snippets['title'][] = $app_config['name'];
    
    $system->breadcrumbs->add($app_config['name'], $app_config['index']);
    
    $system->document->snippets['javascript'][] = '  $(document).ready(function() {' . PHP_EOL
                                                . '    if ($("h1")) {' . PHP_EOL
                                                . '      document.title = document.title +" - "+ $("h1:first").text();' . PHP_EOL
                                                . '    }' . PHP_EOL
                                                . '  });';
    
    if (!empty($_GET['doc'])) {
      include(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/' . $_GET['doc']);
    } else {
      include(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/' . $app_config['index']);
    }
    
// Widgets
  } else {
?>

<div id="widgets">
  <ul>
<?php
    foreach ($widgets as $widget) {
      echo '    <li>' . PHP_EOL;
      include(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $widget['dir'] . $widget['file']);
      echo '    </li>' . PHP_EOL;
    }
?>
  </ul>
</div>
<?php
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>