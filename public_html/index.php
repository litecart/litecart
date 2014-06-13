<?php
  define('SEO_REDIRECT', false);
  require_once('includes/app_header.inc.php');
  
  $url_map = array(
    '#^(?:[a-z]{2}/)?(?:index\.php)?$#'                 => array('script' => 'pages/index.inc.php',              'params' => ''),
    '#^(?:[a-z]{2}/)?.*-c-([0-9]+)/?$#'                  => array('script' => 'pages/category.inc.php',           'params' => 'category_id=$1'),
    '#^(?:[a-z]{2}/)?.*-i-([0-9]+)/?$#'                  => array('script' => 'pages/information.inc.php',        'params' => 'manufacturer_id=$1'),
    '#^(?:[a-z]{2}/)?.*-m-([0-9]+)/?$#'                  => array('script' => 'pages/manufacturer.inc.php',       'params' => 'manufacturer_id=$1'),
    '#^(?:[a-z]{2}/)?.*-s-([0-9]+)/?$#'                  => array('script' => 'pages/customer_service.inc.php',   'params' => 'page_id=$1'),
    '#^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$#' => array('script' => 'pages/product.inc.php',            'params' => 'category_id=$1&product_id=$2'),
    '#^(?:[a-z]{2}/)?search/(.*)?$#'                     => array('script' => 'pages/search.inc.php',             'params' => 'query=$1'),
  );
  
  $request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $request = preg_replace('/^'. preg_quote(WS_DIR_HTTP_HOME, '/') .'(.*)$/', "$1", $request);
  
  foreach ($url_map as $match => $properties) {
    if (preg_match($match, $request)) {
      
      if (!empty($properties['params'])) {
        parse_str(preg_replace($match, $properties['params'], $request), $params);
        $_GET = array_merge($_GET, $params);
      }
      
      $route = $properties;
      break;
    }
  }
  
  if (!empty($route)) {
    include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $route['script']);
  } else {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>HTTP 404 - File Not Found</h1>';
    echo '<p>Could not find a mathing reference for '. $request .'.</p>';
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>