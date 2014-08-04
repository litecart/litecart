<?php
  
  class route {
    public static $request = '';
    public static $routes = array();
    public static $route = array();
    
    public static function construct() {
     
    // Route
      $request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
      $request = preg_replace('/^'. preg_quote(WS_DIR_HTTP_HOME, '/') .'(.*)$/', "$1", $request);
      self::$request = $request;
    }
    
    //public static function load_dependencies() {
    //}
    
    public static function initiate() {
      $routes = array(
        '#^(?:[a-z]{2}/)?(?:index\.php)?$#'                  => array('script' => 'routes/index.inc.php',              'params' => ''),
        '#^(?:[a-z]{2}/)?.*-c-([0-9]+)/?$#'                  => array('script' => 'routes/category.inc.php',           'params' => 'category_id=$1'),
        '#^(?:[a-z]{2}/)?.*-i-([0-9]+)/?$#'                  => array('script' => 'routes/information.inc.php',        'params' => 'manufacturer_id=$1'),
        '#^(?:[a-z]{2}/)?.*-m-([0-9]+)/?$#'                  => array('script' => 'routes/manufacturer.inc.php',       'params' => 'manufacturer_id=$1'),
        '#^(?:[a-z]{2}/)?.*-s-([0-9]+)/?$#'                  => array('script' => 'routes/customer_service.inc.php',   'params' => 'page_id=$1'),
        '#^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$#' => array('script' => 'routes/product.inc.php',            'params' => 'category_id=$1&product_id=$2'),
        '#^(?:[a-z]{2}/)?ajax/(.*)(\.php)?$#'                => array('script' => 'ajax/$1.php',                       'params' => ''),
        '#^(?:[a-z]{2}/)?search/(.*)?$#'                     => array('script' => 'routes/search.inc.php',             'params' => 'query=$1'),
        
        '#^(?:[a-z]{2}/)?test$#'                             => array('script' => 'routes/test.inc.php',               'params' => ''),
        '#^(?:[a-z]{2}/)?([0-9|a-z|_]+)(\.php)?$#'                       => array('script' => 'routes/$1.inc.php',                 'params' => ''),
      );
      
      foreach ($routes as $key => $properties) {
        self::$routes[$key] = $properties;
      }
    }
    
    public static function startup() {
    }
    
    public static function before_capture() {
    
      foreach (self::$routes as $match => $properties) {
        
        if (!preg_match($match, self::$request)) continue;
          
        $properties['script'] = preg_replace($match, $properties['script'], self::$request);
        
        if (!empty($properties['params'])) {
          parse_str(preg_replace($match, $properties['params'], self::$request), $params);
          $_GET = array_merge($_GET, $params);
        }
        
        self::$route = $properties;
        break;
      }
    }
    
    public static function after_capture() {}
    
    public static function prepare_output() {}
    
    public static function before_output() {}
    
    public static function shutdown() {}
  }
  
?>