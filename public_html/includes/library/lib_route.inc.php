<?php

  class route {
    private static $_classes = array();
    private static $_links_cache = array();
    private static $_links_cache_token;
    private static $_routes = array();
    public static $route = array();
    public static $request = '';

    //public static function construct() {}

    //public static function load_dependencies() {}

    public static function initiate() {

    // Load cached links (url rewrites)
      self::$_links_cache_token = cache::token('links', array('site', 'language'), 'file');
      self::$_links_cache = cache::get(self::$_links_cache_token);

    // Load external/dynamic routes
      $files = glob(FS_DIR_APP . 'includes/routes/url_*.inc.php');

      foreach($files as $file) {
        $file = str_replace("\\", '/', $file); // Convert windows paths
        $route_name = preg_replace('#^.*/url_(.*)\.inc\.php$#', '$1', $file);
        $class_name = preg_replace('#^.*/(url_.*)\.inc\.php$#', '$1', $file);

        self::$_classes[$route_name] = new $class_name;

        if (method_exists(self::$_classes[$route_name], 'routes')) {
          $routes = self::$_classes[$route_name]->routes();

          if (!empty($routes)) {
            foreach ($routes as $route) {
              self::$_routes[$route['pattern']] = array(
                'page' => $route['page'],
                'params' => !empty($route['params']) ? $route['params'] : '',
                'redirect' => !empty($route['redirect']) ? true : false,
              );
            }
          }
        }
      }

    // Append default routes
      $routes = array(
        '#^order_process$#'                    => array('page' => 'order_process',  'params' => '',  'redirect' => false, 'post_security' => false),
        '#^([0-9a-zA-Z_/\.]+)(?:\.php)?$#'     => array('page' => '$1',             'params' => '',  'redirect' => true),
        // See ~/includes/routes/ folder for more advanced routes
      );

      foreach ($routes as $pattern => $route) {
        self::$_routes[$pattern] = $route;
      }
    }

    public static function startup() {

    // Neutralize request path (removes logical prefixes)
      self::$request = self::strip_url_logic($_SERVER['REQUEST_URI']);

    // Abort mission if in admin panel
      if (preg_match('#^'. preg_quote(ltrim(WS_DIR_ADMIN, '/'), '#') .'.*#', self::$request)) return;

    // Set target route for requested URL
      foreach (self::$_routes as $matched_pattern => $route) {

        if (!preg_match($matched_pattern, self::$request)) continue;

        $route['page'] = preg_replace($matched_pattern, $route['page'], self::$request);

        if (!is_file(FS_DIR_APP . 'pages/' . $route['page'] .'.inc.php')) continue;

        if (!empty($route['params'])) {
          mb_parse_str(preg_replace($matched_pattern, $route['params'], self::$request), $params);
          $_GET = array_merge($_GET, $params);
        }

        self::$route = $route;
        break;
      }

    // Forward to rewritten URL (if necessary)
      if (!empty(self::$route['page']) && is_file(vmod::check(FS_DIR_APP . 'pages/' . self::$route['page'] .'.inc.php'))) {

        $rewritten_url = document::ilink(self::$route['page'], $_GET);

        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != parse_url($rewritten_url, PHP_URL_PATH)) {

          $do_redirect = true;

        // Don't forward if there is HTTP POST data
          if (file_get_contents('php://input') != '') $do_redirect = false;

        // Don't forward if requested not to
          if (isset(self::$route['redirect']) && self::$route['redirect'] != true) $do_redirect = false;

        // Don't forward if there are notices in stack
          if (!empty(notices::$data)) {
            foreach (notices::$data as $notices) {
              if (!empty($notices)) $do_redirect = false;
            }
          }

          if ($do_redirect) {
            if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == WS_DIR_APP) {
              header('Location: '. $rewritten_url, true, 302);
            } else {
              header('Location: '. $rewritten_url, true, 301);
            }
            exit;
          }
        }
      }
    }

    //public static function before_capture() {
    //}

    public static function after_capture() {
      cache::set(self::$_links_cache_token, self::$_links_cache);
    }

    //public static function prepare_output() {}

    //public static function before_output() {}

    //public static function after_shutdown() {}

    //public static function shutdown() {}

    ######################################################################

    public static function strip_url_logic($link) {

      if (empty($link)) return;

      $link = parse_url($link, PHP_URL_PATH);

      $link = preg_replace('#^'. WS_DIR_APP . '(index\.php/)?(('. implode('|', array_keys(language::$languages)) .')/)?(.*)$#', "$4", $link);

      return $link;
    }

    public static function create_link($path=null, $new_params=array(), $inherit_params=null, $skip_params=array(), $language_code=null, $rewrite=false) {

      if (empty($language_code)) $language_code = language::$selected['code'];

      $link = new ent_link((string)$path);

      if ($path === null && $inherit_params === null) $inherit_params = true;

    // Remove index file from links
      $link->path = preg_replace('#/(index\.php)$#', '', $link->path);

    // Set params that are inherited from the current page
      if ($inherit_params === true) {
        $link->query = $_GET;
      } else if (is_array($inherit_params)) {
        foreach ($_GET as $key => $value) {
          if (in_array($key, $inherit_params)) {
            $link->set_query($key, $value);
          }
        }
      }

    // Unset params that are to be skipped from the link
      if (is_string($skip_params)) $skip_params = array($skip_params);
      foreach ($skip_params as $key) {
        if (isset($link->query[$key])) $link->unset_query($key);
      }

    // Set new params (overwrites any existing inherited params)
      foreach ($new_params as $key => $value) {
        $link->set_query($key, $value);
      }

    // Rewrite URL
      if ($rewrite) {
        if ($link->host == $_SERVER['HTTP_HOST']) {
          if (preg_match('#^'. WS_DIR_APP .'#', $link->path)) {
            return self::rewrite($link, $language_code);
          }
        }
      }

      return (string)$link;
    }

    public static function rewrite(object $link, $language_code=null) {

      if ($link->host != $_SERVER['HTTP_HOST']) return $link;

      if (empty($language_code)) {
        $language_code = language::$selected['code'];
      }

      if (!empty($link->query['language'])) {
        $language_code = $link->query['language'];
      }

      if (!in_array($language_code, array_keys(language::$languages))) {
        trigger_error('Invalid language code ('. $language_code .')', E_USER_WARNING);
        return $link;
      }

      if (isset(self::$_links_cache[$language_code][(string)$link])) return self::$_links_cache[$language_code][(string)$link];

      ###

    // Strip logic from string
      $link->path = self::strip_url_logic($link->path);

    // Don't rewrite links in the admin folder
      if (preg_match('#^'. preg_quote(WS_DIR_ADMIN, '#') .'.*#', $link->path)) return;

    // Set route name
      $route_name = str_replace('/', '_', trim($link->path, '/'));

    // Rewrite link
      if (!empty(self::$_classes[$route_name])) {
        if (method_exists(self::$_classes[$route_name], 'rewrite')) {
          if ($rewritten_link = self::$_classes[$route_name]->rewrite($link, $language_code)) {
            $link = $rewritten_link;
          }
        }
      }

      ###

    // Detect URL rewrite support
      $use_rewrite = false;
      if (isset($_SERVER['REDIRECT_HTTP_MOD_REWRITE']) && in_array(strtolower($_SERVER['REDIRECT_HTTP_MOD_REWRITE']), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
        $use_rewrite = true;

      } else if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
        $use_rewrite = true;

      } else if (preg_match('#(apache)#i', $_SERVER['SERVER_SOFTWARE'])) {
        $use_rewrite = true;
      }

    // Prepend language prefix
      if (count(language::$languages) > 1 && settings::get('seo_links_language_prefix')) {
        if (isset($link->query['language'])) $link->unset_query('language');
        $link->path = $language_code .'/'. ltrim($link->path, '/');
      }

    // Set base (/index.php/ or /)
      if ($use_rewrite) {
        $link->path = WS_DIR_APP . ltrim($link->path, '/');
      } else {
        $link->path = WS_DIR_APP . 'index.php/' . ltrim($link->path, '/');
      }

      self::$_links_cache[$language_code][(string)$link] = $link;

      return self::$_links_cache[$language_code][(string)$link];
    }
  }
