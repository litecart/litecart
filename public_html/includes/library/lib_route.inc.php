<?php

  class route {
    private static $_classes = [];
    private static $_links_cache = [];
    private static $_links_cache_token;
    private static $_routes = [];
    public static $route = [];
    public static $request = '';

    public static function init() {

    // Neutralize request path (removes logical prefixes)
      self::$request = self::strip_url_logic($_SERVER['REQUEST_URI']);

    // Identify the request to a route destination
      self::identify();

    // Load cached links (url rewrites)
      self::$_links_cache_token = cache::token('links', ['site', 'language'], 'memory');

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

      foreach (glob($path) as $file) {
        $name = preg_replace('#^.*/url_(.*)\.inc\.php$#', '$1', $file);
        $class = 'url_'.$name;

        self::$_classes[$name] = new $class;

        if (!method_exists(self::$_classes[$name], 'routes')) continue;
        if (!$routes = self::$_classes[$name]->routes())  continue;

        foreach ($routes as $route) {
          self::add($route['pattern'], $route['page'], $route['params'], $route['options']);
        }
      }
    }

    public static function add($pattern, $page, $params='', $options=[]) {
      self::$_routes[] = [
        'pattern' => $pattern,
        'page' => $page,
        'params' => $params,
        'options' => $options,
      ];
    }

    public static function identify() {

    // Find a target route for requested URL
      foreach (self::$_routes as $route) {

        if (!preg_match($route['pattern'], self::$request)) continue;

        $route['page'] = preg_replace($route['pattern'], $route['page'], self::$request);

        if (!empty($route['params'])) {
          parse_str(preg_replace($route['pattern'], $route['params'], self::$request), $params);
          $_GET = array_filter(array_merge($_GET, $params));
        }

        return self::$route = $route;
      }
    }

    public static function process() {

      if (empty(self::$route)) self::identify();

      if (!empty(self::$route['page'])) {
        $page = FS_DIR_APP . 'pages/' . self::$route['page'] .'.inc.php';
      } else {
        $page = false;
      }

    // Forward to rewritten URL (if necessary)
      if (!empty(self::$route['page']) && is_file(vmod::check($page))) {
        $rewritten_url = document::ilink(self::$route['page'], $_GET);

        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != parse_url($rewritten_url, PHP_URL_PATH)) {

          $do_redirect = true;

        // Don't forward if there is HTTP POST data
          if (file_get_contents('php://input') != '') $do_redirect = false;

        // Don't forward if requested not to
          if (isset(self::$route['options']['redirect']) && self::$route['options']['redirect'] != true) $do_redirect = false;

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

      if (!empty(self::$route) && is_file($page)) {
        include vmod::check($page);

      } else {
        $request = new ent_link(document::link());

        http_response_code(404);

      // Don't return an error page for content with a defined extension (presumably static)
        if (preg_match('#\.[a-z]{2,4}$#', $request->path) && !preg_match('#\.(html?|php)$#', $request->path)) exit;

        $not_found_file = FS_DIR_APP . 'logs/not_found.log';

        $lines = is_file($not_found_file) ? file($not_found_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $lines[] = $request->path;
        $lines = array_unique($lines);

        sort($lines);

        if (count($lines) >= 100) {
          $email = new ent_email();
          $email->add_recipient(settings::get('store_email'))
                ->set_subject('[Not Found Report] '. settings::get('store_name'))
                ->add_body("** This is a report of requests made to your website that did not have a destination. **\r\n\r\n". PLATFORM_NAME .' '. PLATFORM_VERSION ."\r\n\r\n".implode("\r\n", $lines))
                ->send();
          file_put_contents($not_found_file, '');
        } else {
          file_put_contents($not_found_file, implode(PHP_EOL, $lines) . PHP_EOL);
        }

        include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
        include vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
        exit;
      }
    }

    public static function strip_url_logic($path) {

      if (empty($path)) return '';

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

    // Don't rewrite links in the admin folder
      if (preg_match('#^'. preg_quote(BACKEND_ALIAS, '#') .'.*#', $link->path)) return;

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

    // Detect URL rewrite support
      $use_rewrite = false;
      if (isset($_SERVER['REDIRECT_HTTP_MOD_REWRITE']) && filter_var($_SERVER['REDIRECT_HTTP_MOD_REWRITE'], FILTER_VALIDATE_BOOLEAN)) {
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
