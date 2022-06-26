<?php

  class route {

    private static $_classes = [];
    private static $_links_cache = [];
    private static $_links_cache_token;
    private static $_routes = [];
    public static $selected = [];
    public static $request = '';

    public static function init() {

    // Neutralize request path (removes logical prefixes)
      self::$request = self::strip_url_logic($_SERVER['REQUEST_URI']);

    // Load cached links (URL rewrites)
      self::$_links_cache_token = cache::token('links', ['site', 'endpoint', 'language'], 'file', 900);

      if (!self::$_links_cache = cache::get(self::$_links_cache_token)) {
        self::$_links_cache = [];
      }

      event::register('after_capture', [__CLASS__, 'after_capture']);
    }

    public static function after_capture() {
      cache::set(self::$_links_cache_token, self::$_links_cache);
    }

    ######################################################################

    public static function load($path) {

      foreach (functions::file_search($path) as $file) {

        $routes = include $file;

        foreach ($routes as $i => $route) {
          self::add($i, $route);
        }
      }
    }

    public static function add($i, $route) {

      if (empty($route['endpoint'])) {
        if (strpos($i, ':') !== false) {
          switch (true) {
            case (preg_match('#^b(ackend)?:#', $i)):
              $route['endpoint'] = 'backend';
              break;
            default:
              $route['endpoint'] = 'frontend';
              break;
          }
        } else {
          $route['endpoint'] = 'frontend';
        }
      }

      if (strpos($i, ':') === false) {
        switch ($route['endpoint']) {
          case 'frontend': $i = 'f:'.$i;  break;
          case 'backend':  $i = 'b:'.$i;  break;
      }
    }

      self::$_routes[$i] = [
        'pattern' => fallback($route['pattern'], ''),
        'endpoint' => fallback($route['endpoint'], 'frontend'),
        'controller' => fallback($route['controller']),
        'params' => fallback($route['params'], []),
        'options' => fallback($route['options'], []),
        'rewrite' => fallback($route['rewrite']),
      ];
    }

    public static function identify() {

    // Find a target route for requested URL
      foreach (self::$_routes as $i => $route) {

        if (!preg_match($route['pattern'], self::$request)) continue;

        if (is_string($route['controller'])) {
          $route['controller'] = preg_replace($route['pattern'], $route['controller'], self::$request);
        }

        if (!empty($route['params'])) {
          parse_str(preg_replace($route['pattern'], $route['params'], self::$request), $params);
          $_GET = array_filter(array_merge($_GET, $params));
        }

        if (preg_match('#:\*$#', $i)) {
          return self::$selected = array_merge(['route' => route::$request], $route);
        } else {
          return self::$selected = array_merge(['route' => $i], $route);
        }
      }
    }

    public static function process() {

      if (empty(self::$selected)) self::identify();

    // Forward to rewritten URL (if necessary)
      if (!empty($selected)) {
        $rewritten_url = document::ilink(self::$selected['route'], $_GET);

        if (self::$request != parse_url($rewritten_url, PHP_URL_PATH)) {

          $do_redirect = true;

        // Don't forward if there is HTTP POST data
          if (file_get_contents('php://input') != '') $do_redirect = false;

        // Don't forward if requested not to
          if (isset(self::$selected['options']['redirect']) && self::$selected['options']['redirect'] != true) $do_redirect = false;

        // Don't forward if there are notices in stack
          if (!empty(notices::$data)) {
            foreach (notices::$data as $notices) {
              if (!empty($notices)) $do_redirect = false;
            }
          }

          if ($do_redirect) {

          // Send HTTP 302 if it's the start page
            if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == WS_DIR_APP) {
              header('Location: '. $rewritten_url, true, 302);
              exit;
            }

            header('Location: '. $rewritten_url, true, 301);
            exit;
          }
        }
      }

    // Execute a callable controller
      if (is_callable(self::$selected['controller'])) {
        call_user_func(self::$selected['controller']);
        return;
      }

    // Execute a file controller
      if (!empty(self::$selected['controller']) && is_string(self::$selected['controller'])) {

        if (is_file(self::$selected['controller'])) {
          $page = self::$selected['controller'];
        } else {
          if (!empty(self::$selected['endpoint']) && self::$selected['endpoint'] == 'backend') {
            $page = 'app://backend/pages/' . self::$selected['controller'] .'.inc.php';
          } else {
            $page = 'app://frontend/pages/' . self::$selected['controller'] .'.inc.php';
          }
        }

        if (is_file($page)) {
          (function(){
            include func_get_arg(0);
          })($page);

          return;
        }
      }

    // Display error document
      http_response_code(404);

      $request = new ent_link(document::link());

    // Don't return an error document for content with a defined extension (presumably static)
      if (preg_match('#\.[a-z]{2,4}$#', $request->path) && !preg_match('#\.(html?|php)$#', $request->path)) exit;

      $not_found_file = 'storage://logs/not_found.log';

      $lines = is_file($not_found_file) ? file($not_found_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
      $lines[] = $request->path;
      $lines = array_unique($lines);

      sort($lines);

      if (count($lines) >= 100) {
        $email = new ent_email();
        $email->add_recipient(settings::get('site_email'))
              ->set_subject('[Not Found Report] '. settings::get('site_name'))
              ->add_body("** This is a report of requests made to your website that did not have a destination. **\r\n\r\n". PLATFORM_NAME .' '. PLATFORM_VERSION ."\r\n\r\n".implode("\r\n", $lines))
              ->send();
        file_put_contents($not_found_file, '');
      } else {
        file_put_contents($not_found_file, implode(PHP_EOL, $lines) . PHP_EOL);
      }

      include 'app://frontend/pages/error_document.inc.php';
      include 'app://includes/app_footer.inc.php';
      exit;

      return;
    }

    public static function strip_url_logic($path) {

      if (empty($path)) return '';

      $path = str_replace('//', '/', $path);

      if ($path = parse_url($path, PHP_URL_PATH)) {
        $path = preg_replace('#^'. WS_DIR_APP . '(index\.php/)?(('. implode('|', array_keys(language::$languages)) .')/)?(.*)$#', "$4", $path);
      }

      return $path;
    }

    public static function create_link($path=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null, $rewrite=false) {

      if (empty($language_code)) $language_code = language::$selected['code'];

      $link = new ent_link((string)$path);

      if ($path === null && $inherit_params === null) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $inherit_params = true;
      }

    // Set params that are inherited from the current page
      if ($inherit_params === true) {
        foreach ($_GET as $key => $value) {
          if (in_array($key, ['country', 'currency', 'language'])) continue;
          $link->set_query($key, $value);
        }
      } else if (is_array($inherit_params)) {
        foreach ($_GET as $key => $value) {
          if (in_array($key, $inherit_params)) {
            $link->set_query($key, $value);
          }
        }
      }

    // Unset params that are to be skipped from the link
      if (is_string($skip_params)) $skip_params = [$skip_params];
      foreach ($skip_params as $key) {
        if (isset($link->query[$key])) $link->unset_query($key);
      }

    // Set new params (overwrites any existing inherited params)
      if (!empty($new_params)) {
        foreach ($new_params as $key => $value) {
          $link->set_query($key, $value);
        }
      }

    // Rewrite URL
      if ($rewrite) {
        if ($link->host == $_SERVER['HTTP_HOST']) {
          if (preg_match('#^'. WS_DIR_APP .'#', $link->path)) {
            return self::rewrite($link, $language_code);
          }
        }
      }

      return $link;
    }

    public static function rewrite(ent_link $link, $language_code=null) {

      if ($link->host != $_SERVER['HTTP_HOST']) return $link;

      if (empty($language_code)) {
        $language_code = language::$selected['code'];
      }

      if (!empty($link->query['language'])) {
        $language_code = $link->query['language'];
      }

      if (!in_array($language_code, array_keys(language::$languages))) {
        $language_code = language::identify();
      }

      if (isset(self::$_links_cache[$language_code][(string)$link])) return self::$_links_cache[$language_code][(string)$link];

    // Strip logic from string
      $link->path = self::strip_url_logic($link->path);

    // Rewrite link
      foreach (self::$_routes as $i => $route) {
        if (empty($route['pattern'])) continue;
        if (preg_match('#^'. strtr(preg_quote($route['pattern'], '#'), ['\\*' => '.*', '\\?' => '.', '\\{' => '(', '\\}' => ')', ',' => '|']) .'$#i', $link->path)) { // Use preg_match() as fnmatch() does not support GLOB_BRACE
          if (method_exists($route, 'rewrite') && $rewritten_link = call_user_func_array($route->rewrite, [$link, $language_code])) {
            $link = $rewritten_link;
          }
        }
      }

    // Detect URL rewrite support
      $use_rewrite = false;
      if (isset($_SERVER['MOD_REWRITE']) && in_array(strtolower($_SERVER['MOD_REWRITE']), ['1', 'active', 'enabled', 'on', 'true', 'yes'])) {
        $use_rewrite = true;

      } else if (isset($_SERVER['REDIRECT_MOD_REWRITE']) && in_array(strtolower($_SERVER['REDIRECT_MOD_REWRITE']), ['1', 'active', 'enabled', 'on', 'true', 'yes'])) {
        $use_rewrite = true;

      } else if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
        $use_rewrite = true;

      } else if (preg_match('#(apache)#i', $_SERVER['SERVER_SOFTWARE'])) {
        $use_rewrite = true;
      }

    // Set language to URL
      switch (language::$languages[$language_code]['url_type']) {

        case 'path':
          if (isset($link->query['language'])) $link->unset_query('language');
          $link->path = $language_code .'/'. ltrim($link->path, '/');
          break;

        case 'domain':
          if (isset($link->query['language'])) $link->unset_query('language');
          $link->host = language::$languages[$language_code]['domain_name'];
          break;
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
