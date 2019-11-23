<?php

  class document {

    public static $template = '';
    public static $layout = 'default';
    public static $snippets = array();
    public static $settings = array();
    public static $jsenv = array();

    public static function init() {
      event::register('before_capture', array(__CLASS__, 'before_capture'));
      event::register('prepare_output', array(__CLASS__, 'prepare_output'));
      event::register('before_output',  array(__CLASS__, 'before_output'));
    }

    public static function before_capture() {

      header('X-Powered-By: '. PLATFORM_NAME);

    // Set template
      if (preg_match('#^('. preg_quote(WS_DIR_ADMIN, '#') .')#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
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
      self::$snippets['text_direction'] = in_array(language::$selected['code'], array('ar', 'he')) ? 'rtl' : 'ltr';
      self::$snippets['charset'] = language::$selected['charset'];
      self::$snippets['home_path'] = WS_DIR_APP;
      self::$snippets['template_path'] = WS_DIR_TEMPLATE;

      self::$snippets['title'] = array(settings::get('store_name'));

      self::$snippets['head_tags']['favicon'] = '<link rel="shortcut icon" href="'. WS_DIR_APP . 'favicon.ico">';

    // CDN content
      //self::$snippets['head_tags']['dns-prefetch-jsdelivr'] = '<link rel="dns-prefetch" href="//cdn.jsdelivr.net">';
      //self::$snippets['head_tags']['fontawesome'] = '<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/font-awesome@4/css/font-awesome.min.css" />';
      //self::$snippets['foot_tags']['jquery'] = '<script src="//cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js"></script>';

    // Local content
      self::$snippets['head_tags']['fontawesome'] = '<link rel="stylesheet" href="'. WS_DIR_APP .'ext/fontawesome/font-awesome.min.css" />';
      self::$snippets['foot_tags']['jquery'] = '<script src="'. WS_DIR_APP .'ext/jquery/jquery-3.4.1.min.js"></script>';

    // Hreflang
      if (!empty(route::$route['page']) && settings::get('seo_links_language_prefix')) {
        if (count(language::$languages) > 1) {
          self::$snippets['head_tags']['hreflang'] = '';
          foreach (array_keys(language::$languages) as $language_code) {
            if ($language_code == language::$selected['code']) continue;
            self::$snippets['head_tags']['hreflang'] .= '<link rel="alternate" hreflang="'. $language_code .'" href="'. document::href_ilink(route::$route['page'], array(), true, array(), $language_code) .'" />' . PHP_EOL;
          }
          self::$snippets['head_tags']['hreflang'] = trim(self::$snippets['head_tags']['hreflang']);
        }
      }

    // Get template settings
      $template_config = include vmod::check(FS_DIR_APP .'includes/templates/'. settings::get('store_template_catalog') .'/config.inc.php');
      if (!is_array($template_config)) include vmod::check(FS_DIR_APP .'includes/templates/'. settings::get('store_template_catalog') .'/config.inc.php'); // Backwards compatibility

      self::$settings = @json_decode(settings::get('store_template_catalog_settings'), true);

      foreach (array_keys($template_config) as $i) {
        if (!isset(self::$settings[$template_config[$i]['key']])) {
          self::$settings[$template_config[$i]['key']] = $template_config[$i]['default_value'];
        }
      }
    }

    public static function prepare_output() {

    // JavaScript Environment
      self::$jsenv['platform'] = array(
        'url' => document::ilink(''),
      );

      self::$jsenv['session'] = array(
        'language_code' => language::$selected['code'],
        'country_code' => customer::$data['country_code'],
        'currency_code' => currency::$selected['code'],
      );

      self::$jsenv['template'] = array(
        'url' => document::link(WS_DIR_TEMPLATE),
        'settings' => self::$settings,
      );

      self::$jsenv['customer'] = array(
        'id' => !empty(customer::$data['id']) ? customer::$data['id'] : null,
        'name' => !empty(customer::$data['firstname']) ? customer::$data['firstname'] .' '. customer::$data['lastname'] : null,
        'email' => !empty(customer::$data['email']) ? customer::$data['email'] : null,
      );

      self::$snippets['head_tags'][] = "<script>var _env = ". json_encode(self::$jsenv, JSON_UNESCAPED_SLASHES) .", config = _env;</script>";

    // Prepare title
      if (!empty(self::$snippets['title'])) {
        if (!is_array(self::$snippets['title'])) self::$snippets['title'] = array(self::$snippets['title']);
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

    // Extract and group in content stylesheets
      if (preg_match('#<html(?:[^>]+)?>(.*)</html>#is', $GLOBALS['output'], $matches)) {
        $content = $matches[1];

        $stylesheets = array();
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

        $styles = array();
        if (preg_match_all('#<style>(.*?)</style>\R?#is', $content, $matches, PREG_SET_ORDER)) {
          foreach ($matches as $match) {
            if ($GLOBALS['output'] = $mb_str_replace_first($match[0], '', $GLOBALS['output'])) {
              $styles[] = trim($match[1]);
            }
          }

          if (!empty($styles)) {
            $styles = '<style>' . PHP_EOL
                   . '<!--/*--><![CDATA[/*><!--*/' . PHP_EOL
                   . implode(PHP_EOL . PHP_EOL, $styles) . PHP_EOL
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

        $js_resources = array();
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

        $javascript = array();
        if (preg_match_all('#<script(?:[^>]*\stype="(?:application|text)/javascript")?>(?!</script>)(.*?)</script>\R?#is', $content, $matches, PREG_SET_ORDER)) {

          foreach ($matches as $match) {
            if ($GLOBALS['output'] = $mb_str_replace_first($match[0], '', $GLOBALS['output'])) {
              $javascript[] = trim($match[1], "\r\n");
            }
          }

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

    public static function ilink($route=null, $new_params=array(), $inherit_params=null, $skip_params=array(), $language_code=null) {

      if ($route === null) {
        $route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($inherit_params === null) $inherit_params = true;
      } else {
        $route = WS_DIR_APP . $route;
      }

      return route::create_link($route, $new_params, $inherit_params, $skip_params, $language_code, true);
    }

    public static function href_ilink($route=null, $new_params=array(), $inherit_params=null, $skip_params=array(), $language_code=null) {
      return htmlspecialchars(self::ilink($route, $new_params, $inherit_params, $skip_params, $language_code));
    }

    public static function link($path=null, $new_params=array(), $inherit_params=null, $skip_params=array(), $language_code=null) {

      if (empty($path)) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($inherit_params === null) $inherit_params = true;
      }

      return route::create_link($path, $new_params, $inherit_params, $skip_params, $language_code, false);
    }

    public static function href_link($path=null, $new_params=array(), $inherit_params=null, $skip_params=array(), $language_code=null) {
      return htmlspecialchars(self::link($path, $new_params, $inherit_params, $skip_params, $language_code));
    }
  }
