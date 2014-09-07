<?php
  require_once('includes/app_header.inc.php');
  
  $file = FS_DIR_HTTP_ROOT . WS_DIR_PAGES . route::$route['page'] .'.inc.php';
  
  if (!empty(route::$route) && is_file($file)) {
    
    include vqmod::modcheck($file);
     
  } else {
  
    header('HTTP/1.1 404 Not Found');
    
    if (preg_match('#\.(jpg|png|gif)$#', route::$request)) exit;
    
    echo '<h1>HTTP 404 - File Not Found</h1>';
    echo '<p>Could not find a mathing reference for '. parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) .'.</p>';
  }
  
  require_once vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>