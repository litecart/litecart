<?php
  require_once('../includes/app_header.inc.php');
  
  user::require_login();
  
  document::$template = settings::get('store_template_admin');
  
  breadcrumbs::add(language::translate('title_admin_panel', 'Admin Panel'), WS_DIR_ADMIN);
  
  if (empty($_SERVER['REDIRECT_REMOTE_USER']) && empty($_SERVER['REMOTE_USER'])) {
    notices::add('warnings', language::translate('warning_admin_folder_not_protected', 'Warning: Your admin folder is not .htaccess protected'), 'unprotected');
  }
  
  if (file_exists(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'install/')) {
    notices::add('warnings', language::translate('warning_install_folder_exists', 'Warning: The installation directory is still available and should be deleted.'), 'install_folder');
  }
  
// Build apps list menu
  $apps_list = '<div id="apps-wrapper">' . PHP_EOL
             . '  <ul id="apps" class="list-vertical">';
  
  foreach (functions::admin_get_apps() as $app) {
    $params = array('app' => $app['code'], 'doc' => $app['default']);
    
    if (empty($app['theme']['icon'])) $app['theme']['icon'] = 'plus';
    if (empty($app['theme']['color'])) $app['theme']['color'] = '#97a3b5';
    
    $apps_list .= '    <li id="app-'. $app['code'] .'"'. ((isset($_GET['app']) && $_GET['app'] == $app['code']) ? ' class="selected"' : '') .'>'. PHP_EOL
                . '      <a href="'. document::href_link(WS_DIR_ADMIN, $params) .'">' . PHP_EOL
                . '      <span class="fa-stack fa-lg icon-wrapper">' . PHP_EOL
                . '        ' . functions::draw_fontawesome_icon('circle', 'style="color: '. $app['theme']['color'] .';"', 'fa-stack-2x icon-background') . PHP_EOL
                . '        ' . functions::draw_fontawesome_icon($app['theme']['icon'], 'style="color: #fff;"', 'fa-stack-1x icon') . PHP_EOL
                . '      </span>' . PHP_EOL
                . '      <span class="name">'. $app['name'] .'</span>' . PHP_EOL
                . '    </a>' . PHP_EOL;
    
    if (!empty($_GET['app']) && $_GET['app'] == $app['code']) {
      
      if (!empty($app['menu'])) {
        $apps_list .= '      <ul class="docs">' . PHP_EOL;
        
        foreach ($app['menu'] as $item) {
          
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
          
          $params = !empty($item['params']) ? array_merge(array('app' => $app['code'], 'doc' => $item['doc']), $item['params']) : array('app' => $app['code'], 'doc' => $item['doc']);
          $apps_list .= '        <li id="doc-'. $item['doc'] .'"'. ($selected ? ' class="selected"' : '') .'><a href="'. document::href_link(WS_DIR_ADMIN, $params) .'"><span class="name">'. $item['title'] .'</span></a></li>' . PHP_EOL;
        }
        
        $apps_list .= '      </ul>' . PHP_EOL;
      }
    }
    
    $apps_list .= '    </li>' . PHP_EOL;
  }
  
  $apps_list .= '  </ul>' . PHP_EOL
            . '</div>';
  
  document::$snippets['apps'] = $apps_list;
  
// App content
  if (!empty($_GET['app'])) {
    
    require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/config.inc.php');
    
    if (empty($app_config['theme']['icon'])) $app_config['theme']['icon'] = 'plus';
    if (empty($app_config['theme']['color'])) $app_config['theme']['color'] = '#97a3b5';
    $rgb = sscanf($app_config['theme']['color'], "#%02x%02x%02x");
    
    breadcrumbs::add($app_config['name'], $app_config['default']);
    
    document::$snippets['javascript'][] = '  $(document).ready(function() {' . PHP_EOL
                                                . '    if ($("h1")) {' . PHP_EOL
                                                . '      if (document.title.substring(0, $("h1:first").text().length) == $("h1:first").text()) return;' . PHP_EOL
                                                . '      document.title = $("h1:first").text() +" | "+ document.title;' . PHP_EOL
                                                . '    }' . PHP_EOL
                                                . '  });';
                                                
    $app_icon = '      <span class="fa-stack fa-lg icon-wrapper">' . PHP_EOL
              . '        ' . functions::draw_fontawesome_icon('circle', 'style="color: '. $app_config['theme']['color'] .';"', 'fa-stack-2x icon-background') . PHP_EOL
              . '        ' . functions::draw_fontawesome_icon($app_config['theme']['icon'], 'style="color: #fff;"', 'fa-stack-1x icon') . PHP_EOL
              . '      </span>' . PHP_EOL;
    
    echo '<style>' . PHP_EOL
       . '#content {' . PHP_EOL
       . '  background: rgb(210,215,222); /* Old browsers */' . PHP_EOL
       . '  background: -moz-linear-gradient(-45deg,  rgba('.$rgb[0].','.$rgb[1].','.$rgb[2].',1) 0px, rgba(255,255,255,1) 100px); /* FF3.6+ */' . PHP_EOL
       . '  background: -webkit-gradient(linear, left top, right bottom, color-stop(0px,rgba('.$rgb[0].','.$rgb[1].','.$rgb[2].',1)), color-stop(100px,rgba(255,255,255,1))); /* Chrome,Safari4+ */' . PHP_EOL
       . '  background: -webkit-linear-gradient(-45deg,  rgba('.$rgb[0].','.$rgb[1].','.$rgb[2].',1) 0px,rgba(255,255,255,1) 100px); /* Chrome10+,Safari5.1+ */' . PHP_EOL
       . '  background: -o-linear-gradient(-45deg,  rgba('.$rgb[0].','.$rgb[1].','.$rgb[2].',1) 0px,rgba(255,255,255,1) 100px); /* Opera 11.10+ */' . PHP_EOL
       . '  background: -ms-linear-gradient(-45deg,  rgba('.$rgb[0].','.$rgb[1].','.$rgb[2].',1) 0px,rgba(255,255,255,1) 100px); /* IE10+ */' . PHP_EOL
       . '  background: linear-gradient(135deg,  rgba('.$rgb[0].','.$rgb[1].','.$rgb[2].',1) 0px,rgba(255,255,255,1) 100px); /* W3C */' . PHP_EOL
       . '  filter: progid:DXImageTransform.Microsoft.gradient( startColorstr=\''. $app_config['theme']['color'] .'\', endColorstr=\'#ffffff\',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */' . PHP_EOL
       . '}' . PHP_EOL
       . '</style>';
       
    echo '<span style="float: right; margin-left: 10px;"><a href="'. document::href_link('http://wiki.litecart.net/', array('title' => 'Admin:'. $_GET['app'] . (!empty($_GET['doc']) ? '/' . $_GET['doc'] : ''))) .'" target="_blank" title="'. language::translate('title_help', 'Help') .'">'. functions::draw_fontawesome_icon('question-circle', 'style="font-size: 2em; color: #0099cc;"') .'</a></span>';
    
    if (!empty($_GET['doc'])) {
      if (empty($app_config['docs'][$_GET['doc']]) || !file_exists(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/' . $app_config['docs'][$_GET['doc']])) trigger_error($_GET['app'] .'.app/'. $_GET['doc'] . ' is not a valid admin document', E_USER_ERROR);
      include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/' . $app_config['docs'][$_GET['doc']]);
    } else {
      include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $_GET['app'].'.app/' . $app_config['docs'][$app_config['default']]);
    }
    
// Widgets
  } else {
?>

<div id="widgets-wrapper">
  <ul id="widgets">
<?php
    foreach (functions::admin_get_widgets() as $widget) {
      echo '    <li id="widget-'. basename($widget['dir'], '.widget') .'">' . PHP_EOL;
      include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ADMIN . $widget['dir'] . $widget['file']);
      echo '    </li>' . PHP_EOL;
    }
?>
  </ul>
</div>
<?php
  }
  
  require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>