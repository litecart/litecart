<?php

  class document {

    public static $layout = 'default';
    public static $snippets = [];
    public static $settings = [];
    public static $jsenv = [];

    public static function init() {
      event::register('before_capture', [__CLASS__, 'before_capture']);
      event::register('after_capture', [__CLASS__, 'after_capture']);
      event::register('prepare_output',  [__CLASS__, 'prepare_output']);
      event::register('before_output',  [__CLASS__, 'before_output']);
    }

    public static function before_capture() {

      header('X-Frame-Options: SAMEORIGIN'); // Clickjacking Protection
      header('Content-Security-Policy: frame-ancestors \'self\';'); // Clickjacking Protection
      header('Access-Control-Allow-Origin: '. document::ilink('')); // Only allow HTTP POST data data from own domain
      header('X-Powered-By: '. PLATFORM_NAME);

    // Default to AJAX layout on AJAX request
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        self::$layout = 'ajax';
      }

    // Set some snippets
      self::$snippets['language'] = language::$selected['code'];
      self::$snippets['text_direction'] = language::$selected['direction'];
      self::$snippets['charset'] = mb_http_output();
      self::$snippets['home_path'] = WS_DIR_APP;
      self::$snippets['template_path'] = WS_DIR_TEMPLATE;
      self::$snippets['title'] = [settings::get('site_name')];
      self::$snippets['head_tags']['favicon'] = '<link rel="shortcut icon" href="'. WS_DIR_STORAGE . 'images/favicons/favicon.ico">';
      self::$snippets['head_tags']['webmanifest'] = '<link rel="manifest" href="'. document::href_ilink('webmanifest.json') .'" />';
      self::$snippets['head_tags']['fontawesome'] = '<link rel="stylesheet" href="'. document::href_rlink(FS_DIR_APP .'assets/fontawesome/font-awesome.min.css') .'" />';
      self::$snippets['foot_tags']['jquery'] = '<script src="'. document::href_rlink(FS_DIR_APP .'assets/jquery/jquery-3.6.0.min.js') .'"></script>';

    // Hreflang
      if (!empty(route::$route['page'])) {
        self::$snippets['head_tags']['hreflang'] = '';
        foreach (language::$languages as $language) {
          if ($language['url_type'] == 'none') continue;
          if ($language['code'] == language::$selected['code']) continue;
          self::$snippets['head_tags']['hreflang'] .= '<link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink(route::$route['page'], [], true, ['page', 'sort'], $language['code']) .'" />' . PHP_EOL;
        }
        self::$snippets['head_tags']['hreflang'] = trim(self::$snippets['head_tags']['hreflang']);
      }

    // Get template settings
      if (!$template_config = include vmod::check(FS_DIR_APP .'frontend/templates/'. settings::get('template') .'/config.inc.php')) {
        $template_config = [];
      }

      self::$settings = settings::get('template_settings') ? json_decode(settings::get('template_settings'), true) : [];

      foreach (array_keys($template_config) as $i) {
        if (!isset(self::$settings[$template_config[$i]['key']])) {
          self::$settings[$template_config[$i]['key']] = $template_config[$i]['default_value'];
        }
      }
    }

    public static function after_capture() {

    // JavaScript Environment

      self::$jsenv['platform'] = [
        'path' => WS_DIR_APP,
        'url' => document::ilink('f:'),
      ];

      if (!empty(user::$data['id'])) {
        self::$jsenv['backend'] = [
          'path' => WS_DIR_APP . BACKEND_ALIAS .'/',
          'url' => document::ilink('b:'),
        ];
      }

      self::$jsenv['session'] = [
        'id' => session::get_id(),
        'language_code' => language::$selected['code'],
        'country_code' => customer::$data['country_code'],
        'currency_code' => currency::$selected['code'],
      ];

      self::$jsenv['template'] = [
        'url' => document::link(WS_DIR_TEMPLATE),
        'settings' => self::$settings,
      ];

      self::$jsenv['customer'] = [
        'id' => !empty(customer::$data['id']) ? customer::$data['id'] : null,
        'name' => !empty(customer::$data['firstname']) ? customer::$data['firstname'] .' '. customer::$data['lastname'] : null,
        'email' => !empty(customer::$data['email']) ? customer::$data['email'] : null,
      ];

      if (!empty(user::$data['id'])) {
        self::$jsenv['user'] = [
          'id' => user::$data['id'],
          'username' => user::$data['username'],
          'email' => !empty(user::$data['email']) ? user::$data['email'] : null,
        ];
      }

      self::$snippets['head_tags'][] = "<script>var _env = ". json_encode(self::$jsenv, JSON_UNESCAPED_SLASHES) .";</script>";
    }

    public static function prepare_output() {

    // Prepare title
      if (!empty(self::$snippets['title'])) {
        if (!is_array(self::$snippets['title'])) self::$snippets['title'] = [self::$snippets['title']];
        self::$snippets['title'] = array_filter(self::$snippets['title']);
        self::$snippets['title'] = implode(' | ', array_reverse(self::$snippets['title']));
      }

    // Add meta description
      if (!empty(self::$snippets['description'])) {
        self::$snippets['head_tags'][] = '<meta name="description" content="'. htmlspecialchars(self::$snippets['description']) .'" />';
        unset(self::$snippets['description']);
      }

    // Prepare styles
      if (!empty(self::$snippets['style'])) {
        self::$snippets['style'] = '<style>' . PHP_EOL
                                 . implode(PHP_EOL . PHP_EOL, self::$snippets['style']) . PHP_EOL
                                 . '</style>' . PHP_EOL;
      }

    // Prepare javascript
      if (!empty(self::$snippets['javascript'])) {
        self::$snippets['javascript'] = '<script>' . PHP_EOL
                                      . implode(PHP_EOL . PHP_EOL, self::$snippets['javascript']) . PHP_EOL
                                      . '</script>' . PHP_EOL;
      }

    // Prepare snippets
      foreach (array_keys(self::$snippets) as $snippet) {
        if (is_array(self::$snippets[$snippet])) self::$snippets[$snippet] = implode(PHP_EOL, self::$snippets[$snippet]);
      }
    }

    public static function before_output() {

      stats::start_watch('output_optimization');

    // Extract stylesheets and on page styling
      $GLOBALS['output'] = preg_replace_callback('#(<html[^>]*>)(.*)(</html>)#is', function($matches) use (&$stylesheets, &$styles, &$javascripts, &$javascript) {

        $stylesheets = [];
        $styles = [];

        $matches[2] = preg_replace_callback('#(<link\s(?:[^>]*rel="stylesheet")[^>]*>)\R?#is', function($match) use (&$stylesheets, &$styles) {
          $stylesheets[] = trim($match[1]);
        }, $matches[2]);

        $matches[2] = preg_replace_callback('#<style>(.*?)</style>\R?#is', function($match) use (&$stylesheets, &$styles) {
          $styles[] = trim($match[1]);
        }, $matches[2]);

        return $matches[1] . $matches[2] . $matches[3];
      }, $GLOBALS['output']);

    // Extract javascripts and on page javascripting
      $GLOBALS['output'] = preg_replace_callback('#(<body[^>]*>)(.*)(</body>)#is', function($matches) use (&$javascripts, &$javascript) {

        $javascripts = [];
        $javascript = [];

        $matches[2] = preg_replace_callback('#\R?(<script[^>]+></script>)\R?#is', function($match) use (&$javascripts, &$javascript) {
           $javascripts[] = trim($match[1]);
        }, $matches[2]);

        $matches[2] = preg_replace_callback('#<script(?:[^>]*\stype="(?:application|text)/javascript")?>(?!</script>)(.*?)</script>\R?#is', function($match) use (&$javascripts, &$javascript) {
           $javascript[] = trim($match[1], "\r\n");
        }, $matches[2]);

        return $matches[1] . $matches[2] . $matches[3];
      }, $GLOBALS['output']);

    // Reinsert extracted content
      if (!empty($stylesheets)) {
        $stylesheets = implode(PHP_EOL, $stylesheets) . PHP_EOL;
        $GLOBALS['output'] = preg_replace('#</head>#', addcslashes($stylesheets . '</head>', '\\$'), $GLOBALS['output'], 1);
      }

      if (!empty($styles)) {

      // Minify Inline CSS
        $search_replace = [
          '#/\*(?:.(?!/)|[^\*](?=/)|(?<!\*)/)*\*/#s' => '', // Remove comments
          '#([a-zA-Z0-9 \#=",-:()\[\]]+\{\s*\}\s*)#' => '', // Remove empty selectors
          '#\s+#' => ' ', // Replace multiple whitespace
          '#^\s+#' => ' ', // Replace leading whitespace
          '#\s*([:;{}])\s*#' => '$1',
          '#;}#' => '}',
        ];

        $styles = '<style>' . PHP_EOL
               //. '<!--/*--><![CDATA[/*><!--*/' . PHP_EOL
               . preg_replace(array_keys($search_replace), array_values($search_replace), implode(PHP_EOL . PHP_EOL, $styles)) . PHP_EOL
               //. '/*]]>*/-->' . PHP_EOL
               . '</style>' . PHP_EOL;

        $GLOBALS['output'] = preg_replace('#</head>#', addcslashes($styles . '</head>', '\\$'), $GLOBALS['output'], 1);
      }

      if (!empty($javascripts)) {
        $javascripts = implode(PHP_EOL, $javascripts) . PHP_EOL;
        $GLOBALS['output'] = preg_replace('#</body>#is', addcslashes($javascripts .'</body>', '\\$'), $GLOBALS['output'], 1);
      }

      if (!empty($javascript)) {
        $javascript = '<script>' . PHP_EOL
                    //. '<!--/*--><![CDATA[/*><!--*/' . PHP_EOL
                    . implode(PHP_EOL . PHP_EOL, $javascript) . PHP_EOL
                    //. '/*]]>*/-->' . PHP_EOL
                    . '</script>' . PHP_EOL;

        $GLOBALS['output'] = preg_replace('#</body>#is', addcslashes($javascript . '</body>', '\\$'), $GLOBALS['output'], 1);
      }

    // Define some resources for preloading
      if (preg_match_all('#<(link|script)[^>]+>#', $GLOBALS['output'], $matches)) {

        $preloads = [];
        foreach ($matches[0] as $key => $match) {

          if (!preg_match('#(?<==")(https?:)?//[^"]+(?=")#is', $match, $m)) continue;

          switch ($matches[1][$key]) {
            case 'link':
              if (!preg_match('#stylesheet#', $m[0])) continue 2;
              $preloads[$m[0]] = 'style';
              break;
            case 'script':
              $preloads[$m[0]] = 'script';
              break;
          }
        }

        foreach ($preloads as $link => $type) {
          header('Link: <'.$link.'>; rel=preload; as='.$type, false);
        }
      }

      stats::stop_watch('output_optimization');

    // Remove HTML comments
      $GLOBALS['output'] = preg_replace_callback('#(<html[^>]*>)(.*)(</html>)#is', function() {
        return preg_replace('#<!--.*?-->#ms', '', $GLOBALS['output']);
      }, $GLOBALS['output']);

    // Static domain
      if ($static_domain = settings::get('static_domain')) {
        $GLOBALS['output'] = preg_replace('# (src|href)="(/[^"]+\.(css|eot|gif|ico|jpe?g|js|map|otf|png|svg|ttf|woff2?)(\?[^"]+)?)"#', ' $1="'. rtrim($static_domain, '/') .'$2"', $GLOBALS['output']);
        $GLOBALS['output'] = preg_replace('# (src|href)="(https?://'. preg_quote($_SERVER['HTTP_HOST'], '#') .')(/[^"]+\.(css|eot|gif|ico|jpe?g|js|otf|png|svg|ttf|woff2?)(\?[^"]+)?)(\?[^"]*)?"#', ' $1="'. rtrim($static_domain, '/') .'$3"', $GLOBALS['output']);
      }
    }

    ######################################################################

    public static function ilink($route=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {

      if ($route !== null) {

        if (preg_match('#^b(ackend)?:(.*)$#', $route, $matches)) {
          $endpoint = 'backend';
          $route = $matches[2];

        } else if (preg_match('#^f(rontend)?:(.*)$#', $route, $matches)) {
          $endpoint = 'frontend';
          $route = $matches[2];

        } else {
          $endpoint = !empty(route::$route['endpoint']) ? route::$route['endpoint'] : 'frontend';
        }

        if ($endpoint == 'backend') {
          $route = WS_DIR_APP . BACKEND_ALIAS .'/'. $route;
        } else {
          $route = WS_DIR_APP . $route;
        }

      } else {
        $route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($inherit_params === null) $inherit_params = true;
      }

      return (string)route::create_link($route, $new_params, $inherit_params, $skip_params, $language_code, true);
    }

    public static function href_ilink($route=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {
      return functions::escape_html(self::ilink($route, $new_params, $inherit_params, $skip_params, $language_code));
    }

    public static function link($path=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {

      if (empty($path)) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($inherit_params === null) $inherit_params = true;
      }

      return (string)route::create_link($path, $new_params, $inherit_params, $skip_params, $language_code, false);
    }

    public static function href_link($path=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {
      return functions::escape_html(self::link($path, $new_params, $inherit_params, $skip_params, $language_code));
    }

    public static function rlink($resource) {

      $path = preg_replace('#^('. preg_quote(DOCUMENT_ROOT, '#') .')#', '', str_replace('\\', '/', $resource));

      if (!is_file($resource)) {
        return document::link($path);
      }

      return document::link($path, ['_' => filemtime($resource)]);
    }

    public static function href_rlink($resource) {
      return functions::escape_html(self::rlink($resource));
    }
  }
