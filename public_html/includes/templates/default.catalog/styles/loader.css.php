<?php
  
  ob_start();
  
  header('Content-Type: text/css');
  header('Cache-Control: must-revalidate');
  header('Expires: ' . date('r', (time()+60*60)) . ' GMT');
  
  ######################################################################
  
// Application
  include('application.css');
  include('common.css');
  
  include('boxes.css');
  include('checkout.css');
  include('forms.css');
  include('listing.css');
  include('site_menu.css');
  include('notices.css');
  include('pagination.css');
  include('product.css');
  include('tables.css');
  include('tabs.css');
  
// Overrides
  include('custom.css');

  ######################################################################
  
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
  
  ######################################################################
  
  if (extension_loaded('zlib')) ob_start('ob_gzhandler');
  
  echo $buffer;
?>