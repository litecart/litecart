<?php

  class document {

    public static $template = '';
    public static $layout = 'default';
    public static $snippets = [];
    public static $settings = [];
    public static $jsenv = [];

    public static function init() {
      event::register('before_capture', [__CLASS__, 'before_capture']);
      event::register('prepare_output', [__CLASS__, 'prepare_output']);
      event::register('before_output',  [__CLASS__, 'before_output']);
    }

    public static function before_capture() {

      header('X-Frame-Options: SAMEORIGIN'); // Clickjacking Protection
      header('Content-Security-Policy: frame-ancestors \'self\';'); // Clickjacking Protection
      header('Access-Control-Allow-Origin: '. document::ilink('')); // Only allow HTTP POST data data from own domain
      header('X-Powered-By: '. PLATFORM_NAME);

    // Set template
      if (!empty($_SERVER['REQUEST_URI']) && preg_match('#^('. preg_quote(WS_DIR_ADMIN, '#') .')#', $_SERVER['REQUEST_URI'])) {
        self::$template = settings::get('store_template_admin');
      } else {
        self::$template = settings::get('store_template_catalog');
      }

      define('FS_DIR_TEMPLATE', FS_DIR_APP .'includes/templates/'. self::$template .'/');
      define('WS_DIR_TEMPLATE', WS_DIR_APP .'includes/templates/'. self::$template .'/');

    // Set AJAX layout on AJAX request
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        self::$layout = 'ajax';
      }

    // Set some snippets
      self::$snippets['language'] = language::$selected['code'];
      self::$snippets['text_direction'] = language::$selected['direction'];
      self::$snippets['charset'] = language::$selected['charset'];
      self::$snippets['home_path'] = WS_DIR_APP;
      self::$snippets['template_path'] = WS_DIR_TEMPLATE;
      self::$snippets['title'] = [settings::get('store_name')];
      self::$snippets['head_tags']['favicon'] = implode(PHP_EOL, [
        '<link rel="icon" href="'. document::href_rlink(FS_DIR_STORAGE . 'images/favicons/favicon.ico') .'" type="image/x-icon" sizes="32x32 48x48 64x64 96x96" />',
        '<link rel="icon" href="'. document::href_rlink(FS_DIR_STORAGE . 'images/favicons/favicon-128x128.png') .'" type="image/png" sizes="128x128" />',
        '<link rel="icon" href="'. document::href_rlink(FS_DIR_STORAGE . 'images/favicons/favicon-192x192.png') .'" type="image/png" sizes="192x192" />',
        '<link rel="icon" href="'. document::href_rlink(FS_DIR_STORAGE . 'images/favicons/favicon-256x256.png') .'" type="image/png" sizes="255x255" />',
      ]);
      self::$snippets['head_tags']['fontawesome'] = '<link rel="stylesheet" href="'. document::href_rlink(FS_DIR_APP .'ext/fontawesome/font-awesome.min.css') .'" />';
      self::$snippets['foot_tags']['jquery'] = '<script src="'. document::href_rlink(FS_DIR_APP .'ext/jquery/jquery-3.6.1.min.js') .'"></script>';

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
      $template_config = include vmod::check(FS_DIR_APP .'includes/templates/'. settings::get('store_template_catalog') .'/config.inc.php');
      if (!is_array($template_config)) include vmod::check(FS_DIR_APP .'includes/templates/'. settings::get('store_template_catalog') .'/config.inc.php'); // Backwards compatibility

      self::$settings = settings::get('store_template_catalog_settings') ? json_decode(settings::get('store_template_catalog_settings'), true) : [];

      foreach (array_keys($template_config) as $i) {
        if (!isset(self::$settings[$template_config[$i]['key']])) {
          self::$settings[$template_config[$i]['key']] = $template_config[$i]['default_value'];
        }
      }
    }

    public static function prepare_output() {

    // JavaScript Environment
      self::$jsenv['platform'] = [
        'path' => WS_DIR_APP,
        'url' => document::ilink(''),
      ];

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

      self::$snippets['head_tags'][] = "<script>var _env = ". json_encode(self::$jsenv, JSON_UNESCAPED_SLASHES) .", config = _env;</script>";

    // Prepare title
      if (!empty(self::$snippets['title'])) {
        if (!is_array(self::$snippets['title'])) self::$snippets['title'] = [self::$snippets['title']];
        self::$snippets['title'] = array_filter(self::$snippets['title']);
        self::$snippets['title'] = implode(' | ', array_reverse(self::$snippets['title']));
      }

    // Add meta description
      if (!empty(self::$snippets['description'])) {
        self::$snippets['head_tags'][] = '<meta name="description" content="'. functions::escape_html(self::$snippets['description']) .'" />';
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

      $microtime_start = microtime(true);

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

    // Extract javascript and on page javascripting
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
               . '<!--/*--><![CDATA[/*><!--*/' . PHP_EOL
               . preg_replace(array_keys($search_replace), array_values($search_replace), implode(PHP_EOL . PHP_EOL, $styles)) . PHP_EOL
               . '/*]]>*/-->' . PHP_EOL
               . '</style>' . PHP_EOL;

        $GLOBALS['output'] = preg_replace('#</head>#', addcslashes($styles . '</head>', '\\$'), $GLOBALS['output'], 1);
      }

      if (!empty($javascripts)) {
        $javascripts = implode(PHP_EOL, $javascripts) . PHP_EOL;
        $GLOBALS['output'] = preg_replace('#</body>#is', addcslashes($javascripts .'</body>', '\\$'), $GLOBALS['output'], 1);
      }

      if (!empty($javascript)) {
        $javascript = '<script>' . PHP_EOL
                    . '<!--/*--><![CDATA[/*><!--*/' . PHP_EOL
                    . implode(PHP_EOL . PHP_EOL, $javascript) . PHP_EOL
                    . '/*]]>*/-->' . PHP_EOL
                    . '</script>' . PHP_EOL;

        if (!$GLOBALS['output'] = preg_replace('#</body>#is', addcslashes($javascript . '</body>', '\\$'), $GLOBALS['output'], 1)) {
          trigger_error('Failed extracting javascripts', E_USER_ERROR);
        }
      }

      if (class_exists('stats', false)) {
        stats::set('output_optimization', microtime(true) - $microtime_start);
      }
    }

    ######################################################################

    public static function expires($string=false) {
      if (strtotime($string) > time()) {
        header('Pragma:');
        header('Cache-Control: max-age='. (strtotime($string) - time()));
        header('Expires: '. date('r', strtotime($string)));
        self::$snippets['head_tags']['meta_expire'] = '<meta http-equiv="cache-control" content="public">' .PHP_EOL
                                                    . '<meta http-equiv="expires" content="'. date('r', strtotime($string)) .'">';
      } else {
        header('Cache-Control: no-cache');
        self::$snippets['head_tags']['meta_expire'] = '<meta http-equiv="cache-control" content="no-cache">' . PHP_EOL
                                                    . '<meta http-equiv="expires" content="'. date('r', strtotime($string)) .'">';
      }
    }

    public static function ilink($route=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {

      if ($route === null) {
        $route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($inherit_params === null) $inherit_params = true;
      } else {
        $route = WS_DIR_APP . $route;
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

      if (!preg_match('#^([a-z]:)?[/\\\\]#i', $resource)) {
        $resource = FS_DIR_APP . $resource;
      }

      if (!is_file($resource)) {
        trigger_error('Could not create link for missing resource ('. $resource.')', E_USER_WARNING);
        return document::link(preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', $resource));
      }

      $resource = str_replace('\\', '/', realpath($resource));

      return document::link(preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', $resource), ['_' => filemtime($resource)]);
    }

    public static function href_rlink($resource) {
      return functions::escape_html(self::rlink($resource));
    }
  }
