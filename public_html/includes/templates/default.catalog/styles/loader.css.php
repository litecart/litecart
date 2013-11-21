<?php
  define('SEO_REDIRECT', false);
  require_once('../../../app_header.inc.php');
  
  header('Content-Type: text/css');
  header('Cache-Control: must-revalidate');
  header('Pragma: cache');
  header('Expires: ' . date('r', (time()+60*60*3)) . ' GMT');
  
  $optimize = true;
  
  ######################################################################
  
  $stylesheets = array(
  
  // Application
    'application.css',
    'common.css',
    
    'boxes.css',
    'checkout.css',
    'forms.css',
    'listing.css',
    'site_menu.css',
    'notices.css',
    'pagination.css',
    'product.css',
    'tables.css',
    'tabs.css',
    
  // Dynamics
    'dynamics.css.php',
    
  // Overrides
    'custom.css',
  );
  
  foreach ($stylesheets as $stylesheet) {
    if ($optimize) {
      include($stylesheet);
    } else {
      echo '@import url('. $stylesheet .');' . PHP_EOL;
    }
  }
  
  ######################################################################
  
  if ($optimize) {
    
    $buffer = ob_get_clean();
    
    $patterns = array(
      '#/\*.*?\*/#s' => '',
      '/\s*([{}|:;,])\s+/' => '$1',
      '/\s\s+(.*)/' => '$1',
      '/;\}/' => '}',
      '/\}/' => '}'.PHP_EOL,
    );
    
    foreach ($patterns as $search => $replace) {
      $buffer = preg_replace($search, $replace, $buffer);
    }
    
    if (extension_loaded('zlib')) ob_start('ob_gzhandler');
    
    echo $buffer;
  }
  
  ######################################################################

?>