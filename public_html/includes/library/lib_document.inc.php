<?php

  class document {

    public static $template = 'default';
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

      header('X-Powered-By: '. PLATFORM_NAME);

    // Set template
      if (!empty(route::$route['endpoint']) && route::$route['endpoint'] == 'backend') {
        define('FS_DIR_TEMPLATE', FS_DIR_APP . 'backend/template/');
        define('WS_DIR_TEMPLATE', WS_DIR_APP . 'backend/template/');
      } else {
        define('FS_DIR_TEMPLATE', FS_DIR_APP . 'frontend/templates/'. self::$template .'/');
        define('WS_DIR_TEMPLATE', WS_DIR_APP . 'frontend/templates/'. self::$template .'/');
      }

    // Default to AJAX layout on AJAX request
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        self::$layout = 'ajax';
      }

    // Set some snippets
      self::$snippets['language'] = language::$selected['code'];
      self::$snippets['text_direction'] = in_array(language::$selected['code'], ['ar', 'he']) ? 'rtl' : 'ltr';
      self::$snippets['charset'] = language::$selected['charset'];
      self::$snippets['home_path'] = WS_DIR_APP;
      self::$snippets['template_path'] = WS_DIR_TEMPLATE;

      self::$snippets['title'] = [settings::get('store_name')];

      self::$snippets['head_tags']['favicon'] = '<link rel="shortcut icon" href="'. WS_DIR_APP . 'favicon.ico">';

      self::$snippets['head_tags']['fontawesome'] = '<link rel="stylesheet" href="'. document::href_rlink(FS_DIR_APP .'vendor/fontawesome/font-awesome.min.css') .'" />';
      self::$snippets['foot_tags']['jquery'] = '<script src="'. document::href_rlink(FS_DIR_APP .'vendor/jquery/jquery-3.5.1.min.js') .'"></script>';

    // Hreflang
      if (!empty(route::$route['page']) && settings::get('seo_links_language_prefix')) {
        if (count(language::$languages) > 1) {
          self::$snippets['head_tags']['hreflang'] = '';
          foreach (array_keys(language::$languages) as $language_code) {
            if ($language_code == language::$selected['code']) continue;
            self::$snippets['head_tags']['hreflang'] .= '<link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink(route::$route['page'], [], true, [], $language_code) .'" />' . PHP_EOL;
          }
          self::$snippets['head_tags']['hreflang'] = trim(self::$snippets['head_tags']['hreflang']);
        }
      }

    // Get template settings
      $template_settings = include vmod::check(FS_DIR_APP .'frontend/templates/'. settings::get('store_template') .'/config.inc.php');

      self::$settings = @json_decode(settings::get('store_template_settings'), true);

      foreach ($template_settings as $setting) {
        if (!isset(self::$settings[$setting['key']])) {
          self::$settings[$setting['key']] = $setting['default_value'];
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

      $mb_str_replace_first = function($search, $replace, $subject) {
        return implode($replace, explode($search, $subject, 2));
      };

    // Remove Comments
      if (preg_match('#<html(?:[^>]+)?>(.*)</html>#is', $GLOBALS['output'], $matches)) {
        $GLOBALS['output'] = preg_replace('#(<html(?:[^>]+)?>).*(</html>)#is', '$1'. preg_replace('#<!--.*?-->#ms', '', $matches[1]) .'$2', $GLOBALS['output']);
      }

    // Extract and group in content stylesheets
      if (preg_match('#<html(?:[^>]+)?>(.*)</html>#is', $GLOBALS['output'], $matches)) {
        $content = $matches[1];

        $stylesheets = [];
        if (preg_match_all('#(<link\s(?:[^>]*rel="stylesheet")[^>]*>)\R?#is', $content, $matches, PREG_SET_ORDER)) {
          foreach ($matches as $match) {
            if ($GLOBALS['output'] = $mb_str_replace_first($match[0], '', $GLOBALS['output'])) {
              $stylesheets[] = trim($match[1]);
            }
          }

        if (!empty($stylesheets)) {
            $stylesheets = implode(PHP_EOL, $stylesheets) . PHP_EOL;

            if (!$GLOBALS['output'] = preg_replace('#</head>#', preg_replace('#\$(\d+)#', '\\\$$1', $stylesheets . '</head>'), $GLOBALS['output'], 1)) {
              trigger_error('Failed extracting stylesheets', E_USER_ERROR);
            }
          }
        }
      }

    // Extract and group in content styling
      if (preg_match('#<html(?:[^>]+)?>(.*)</html>#is', $GLOBALS['output'], $matches)) {
        $content = $matches[1];

        $styles = [];
        if (preg_match_all('#<style>(.*?)</style>\R?#is', $content, $matches, PREG_SET_ORDER)) {
          foreach ($matches as $match) {
            if ($GLOBALS['output'] = $mb_str_replace_first($match[0], '', $GLOBALS['output'])) {
              $styles[] = trim($match[1]);
            }
          }

        // Minify Inline CSS
          $search_replace = array(
            '#/\*(?:.(?!/)|[^\*](?=/)|(?<!\*)/)*\*/#s' => '', // Remove comments
            '#([a-zA-Z0-9 \#=",-:()\[\]]+\{\s*\}\s*)#' => '', // Remove empty selectors
            '#\s+#' => ' ', // Replace multiple whitespace
            '#^\s+#' => ' ', // Replace leading whitespace
            '#\s*([:;{}])\s*#' => '$1',
            '#;}#' => '}',
          );

          $styles = preg_replace(array_keys($search_replace), array_values($search_replace), implode(PHP_EOL, $styles));

          if (!empty($styles)) {
            $styles = '<style>' . PHP_EOL
                    . '<!--/*--><![CDATA[/*><!--*/' . PHP_EOL
                    . $styles . PHP_EOL
                    . '/*]]>*/-->' . PHP_EOL
                    . '</style>' . PHP_EOL;

            if (!$GLOBALS['output'] = preg_replace('#</head>#', preg_replace('#\$(\d+)#', '\\\$$1', $styles . '</head>'), $GLOBALS['output'], 1)) {
              trigger_error('Failed extracting styles', E_USER_ERROR);
            }
          }
        }
      }

    // Extract and group javascript resources
      if (preg_match('#<body(?:[^>]+)?>(.*)</body>#is', $GLOBALS['output'], $matches)) {
        $content = $matches[1];

        $js_resources = [];
        if (preg_match_all('#\R?(<script[^>]+></script>)\R?#is', $content, $matches, PREG_SET_ORDER)) {

          foreach ($matches as $match) {
            if ($GLOBALS['output'] = $mb_str_replace_first($match[0], '', $GLOBALS['output'])) {
              $js_resources[] = trim($match[1]);
            }
          }

          if (!empty($js_resources)) {
            $js_resources = implode(PHP_EOL, $js_resources) . PHP_EOL;

            if (!$GLOBALS['output'] = preg_replace('#</body>#is', preg_replace('#\$(\d+)#', '\\\$$1', $js_resources .'</body>'), $GLOBALS['output'], 1)) {
              trigger_error('Failed extracting javascript resources', E_USER_ERROR);
            }
          }
        }
      }

    // Extract and group inline javascript
      if (preg_match('#<body(?:[^>]+)?>(.*)</body>#is', $GLOBALS['output'], $matches)) {
        $content = $matches[1];

        $javascript = [];
        if (preg_match_all('#<script(?:[^>]*\stype="(?:application|text)/javascript")?>(?!</script>)(.*?)</script>\R?#is', $content, $matches, PREG_SET_ORDER)) {

          foreach ($matches as $match) {
            if ($GLOBALS['output'] = $mb_str_replace_first($match[0], '', $GLOBALS['output'])) {
              $javascript[] = trim($match[1], "\r\n");
            }
          }

        // Remove JS comments
          $javascript = preg_replace('#/\*[\s\S]*?\*/|([^:]|^)//.*$#m', '', $javascript);

          if (!empty($javascript)) {
            $javascript = '<script>' . PHP_EOL
                        . '<!--/*--><![CDATA[/*><!--*/' . PHP_EOL
                        . implode(PHP_EOL . PHP_EOL, $javascript) . PHP_EOL
                        . '/*]]>*/-->' . PHP_EOL
                        . '</script>' . PHP_EOL;

            if (!$GLOBALS['output'] = preg_replace('#</body>#is', preg_replace('#\$(\d+)#', '\\\$$1', $javascript . '</body>'), $GLOBALS['output'], 1)) {
              trigger_error('Failed extracting javascripts', E_USER_ERROR);
            }
          }
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

      return route::create_link($route, $new_params, $inherit_params, $skip_params, $language_code, true);
    }

    public static function href_ilink($route=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {
      return htmlspecialchars(self::ilink($route, $new_params, $inherit_params, $skip_params, $language_code));
    }

    public static function link($path=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {

      if (empty($path)) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($inherit_params === null) $inherit_params = true;
      }

      return route::create_link($path, $new_params, $inherit_params, $skip_params, $language_code, false);
    }

    public static function href_link($path=null, $new_params=[], $inherit_params=null, $skip_params=[], $language_code=null) {
      return htmlspecialchars(self::link($path, $new_params, $inherit_params, $skip_params, $language_code));
    }

    public static function rlink($resource) {
      $timestamp = filemtime($resource);
      return document::link(preg_replace('#^('. preg_quote(FS_DIR_APP, '#') .')#', '', str_replace('\\', '/', realpath($resource))), ['_' => $timestamp]);
    }

    public static function href_rlink($resource) {
      return htmlspecialchars(self::rlink($resource));
    }
  }
