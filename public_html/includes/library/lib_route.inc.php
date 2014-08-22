<?php
  
  class route {
    private static $_classes = array();   
    private static $_links_cache = array();
    private static $_links_cache_id = '';
    private static $_routes = array();
    public static $route = array();
    public static $request = '';
    
    //public static function construct() {}
    
    //public static function load_dependencies() {}
    
    //public static function initiate() {}
    
    public static function startup() {
      
    // Load cached links (url rewrites)
      self::$_links_cache_id = cache::cache_id('links', array('language'));
      self::$_links_cache = cache::get(self::$_links_cache_id, 'file');
      
    // Add default routes
      $routes = array(
        '#^(?:index\.php)?$#'                  => array('script' => 'routes/index.inc.php',              'params' => ''),
        '#^ajax/(.*)(?:\.php)?$#'              => array('script' => 'routes/ajax/$1.inc.php',            'params' => ''),
        '#^feeds/(.*)(?:\.php)?$#'             => array('script' => 'routes/feeds/$1.inc.php',           'params' => ''),
        '#^([0-9|a-z|_]+)(?:\.php)?$#'         => array('script' => 'routes/$1.inc.php',                 'params' => ''),
        // See includes/routes/* for more advanced routes
      );
      
      foreach ($routes as $pattern => $properties) {
        self::$_routes[$pattern] = $properties;
      }
      
    // Load external/dynamic routes
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_ROUTES . 'url_*.inc.php');
      
      foreach($files as $file) {
        $route_name = preg_replace('#^.*/url_(.*)\.inc\.php$#', '$1', $file);
        $class_name = preg_replace('#^.*/(url_.*)\.inc\.php$#', '$1', $file);
        
        self::$_classes[$route_name] = new $class_name;
        
        if (method_exists(self::$_classes[$route_name], 'routes')) {
          $routes = self::$_classes[$route_name]->routes();
          
          if (!empty($routes)) {
            foreach ($routes as $route) {
              self::$_routes[$route['pattern']] = array(
                'script' => $route['script'],
                'params' => !empty($route['params']) ? $route['params'] : '',
              );
            }
          }
        }
      }
    }
    
    public static function before_capture() {
    
    // Neutralize request path (removes logical prefixes)
      self::$request = self::strip_url_logic($_SERVER['REQUEST_URI']);
      
    // Abort mission if admin panel
      if (preg_match('#^'. preg_quote(WS_DIR_ADMIN, '#') .'.*#', self::$request)) return;
      
    // Set target route for requested URL
      foreach (self::$_routes as $match => $properties) {
        
        if (!preg_match($match, self::$request)) continue;
          
        $properties['script'] = preg_replace($match, $properties['script'], self::$request);
        
        if (!empty($properties['params'])) {
          parse_str(preg_replace($match, $properties['params'], self::$request), $params);
          $_GET = array_merge($_GET, $params);
        }
        
        self::$route = $properties['script'];
        break;
      }
      
      /*
    // Forward to rewritten URL (if necessary)
      $requested_url = self::rewrite($_SERVER['REQUEST_URI']);
      if (document::link($_SERVER['REQUEST_URI']) != $requested_url) {
        
        $redirect = true;
        
      // Don't forward if there is HTTP POST data
        if (!empty($_POST)) $redirect = false;
        
      // Don't forward if requested not to
        if (defined('SEO_REDIRECT') && SEO_REDIRECT == false) $redirect = false;
        
      // Don't forward if there are notices in stack
        if (!empty(notices::$data)) {
          foreach (notices::$data as $notices) {
            if (!empty($notices)) $redirect = false;
          }
        }
        
        if ($redirect) {
          //error_log('Redirecting user from '. $_SERVER['REQUEST_URI'] .' to'. link::($_SERVER['REQUEST_URI']));
          header('HTTP/1.1 301 Moved Permanently');
          header('Location: '. $requested_url);
          exit;
        }
      }
      */
    }
    
    public static function after_capture() {
      cache::set(self::$_links_cache_id, 'file', self::$_links_cache);
    }
    
    //public static function prepare_output() {}
    
    //public static function before_output() {}
    
    //public static function after_shutdown() {}
    
    //public static function shutdown() {}
    
    ######################################################################
    
    public static function ilink($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return link::create_link($document, $new_params, $inherit_params, $skip_params, $language_code);
    }
    
    public static function href_ilink($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return htmlspecialchars(self::link($document, $new_params, $inherit_params, $skip_params, $language_code));
    }
    
    public static function strip_url_logic($link) {
      
      if (empty($link)) return;
      
      $link = parse_url($link, PHP_URL_PATH);
      
      $link = preg_replace('#^'. WS_DIR_HTTP_HOME .'(index\.php/)?(('. implode('|', array_keys(language::$languages)) .')/)?(.*)$#', "$4", $link);
      
      return $link;
    }
    
    public static function rewrite($link, $language_code=null) {
      
      if (empty($language_code)) $language_code = language::$selected['code'];
      
      if (!in_array($language_code, array_keys(language::$languages))) {
        trigger_error('Invalid language code ('. $language_code .')', E_USER_WARNING);
        return;
      }
      
      if (isset(self::$_links_cache[$language_code][$link])) return self::$_links_cache[$language_code][$link];
      
      $parsed_link = link::explode_link($link);
      
    // Don't override links for external domains
      if ($parsed_link['host'] != preg_replace('#^([a-z|0-9|\.|-]+)(?:\:[0-9]+)?$#', '$1', $_SERVER['HTTP_HOST'])) return;
      
      ###
      
    // Strip logic from string
      $parsed_link['path'] = self::strip_url_logic($parsed_link['path']);
      
    // Don't rewrite links in the admin folder
      if (preg_match('#^'. preg_quote(basename(WS_DIR_ADMIN), '#') .'.*#', $parsed_link['path'])) return;
      
    // Set route name
      $route_name = preg_replace('#^(.*)$/#', '$1', $parsed_link['path']);
      
      if (!empty(self::$_classes[$route_name])) {
      
      // Rewrite url
        if (method_exists(self::$_classes[$route_name], 'rewrite')) {
        
          $rewritten_parsed_link = self::$_classes[$route_name]->rewrite($parsed_link, $language_code);
          
          if (!empty($rewritten_parsed_link)) {
            $parsed_link = $rewritten_parsed_link;
            $parsed_link['path'] = self::strip_url_logic($parsed_link['path']);
          }
        }
      }
      
      ###
      
    // Set home path (Platform root)
      $http_route_base = WS_DIR_HTTP_HOME;
      
    // Append router base (/index.php or /)
      if (!isset($_SERVER['HTTP_MOD_REWRITE']) || !in_array(strtolower($_SERVER['HTTP_MOD_REWRITE']), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
        $http_route_base .= 'index.php/';
      }
      
    // Append language prefix
      if (settings::get('seo_links_language_prefix')) {
        $http_route_base .= $language_code .'/';
      }
      
      $parsed_link['path'] = $http_route_base . $parsed_link['path'];
      self::$_links_cache[$language_code][$link] = link::implode_link($parsed_link);
      
      return self::$_links_cache[$language_code][$link];
    }
  }
  
?>