<?php

  class link {

    public static function construct() {
    }

    //public static function load_dependencies() {
    //}

    //public static function startup() {
    //}

    //public static function initiate() {
    //}

    //public static function before_capture() {
    //}

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function get_physical_link() {
      $link = $_SERVER['SCRIPT_NAME'] . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');

      return self::full_link($link);
    }

    public static function get_logical_link() {
      return self::full_link($_SERVER['REQUEST_URI']);
    }

    public static function create_link($document=null, $new_params=array(), $inherit_params=null, $skip_params=array(), $language_code=null) {

      if (empty($language_code)) $language_code = language::$selected['code'];

    // Parse link
      if ($document === null) {
        $parsed_link = self::explode_link(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if ($inherit_params === null) $inherit_params = true;

      } else if ($document == '') {
        $parsed_link = self::explode_link(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));


      } else if (substr($document, 0, 4) == 'http' || strpos($document, '?') !== false) {
        $parsed_link = self::explode_link($document);

      } else {
        $parsed_link = array(
          'path' => self::fullpath($document),
          'query' => array(),
        );
      }

      if ($inherit_params === null) $inherit_params = false;

    // Clean any double slashes
      while (strpos($parsed_link['path'], '//')) $parsed_link['path'] = str_replace('//', '/', $parsed_link['path']);

    // Remove index file from links
      $parsed_link['path'] = preg_replace('#/(index\.php)$#', '', $parsed_link['path']);

    // Set params that are inherited from the current page
      if ($inherit_params === true) {
        $parsed_link['query'] = $_GET;
      } else if (is_array($inherit_params)) {
        foreach ($_GET as $key => $value) {
          if (in_array($key, $inherit_params)) {
            $parsed_link['query'][$key] = $value;
          }
        }
      }

    // Unset params that are to be skipped from the link
      if (is_string($skip_params)) $skip_params = array($skip_params);
      foreach ($skip_params as $key) {
        if (isset($parsed_link['query'][$key])) unset($parsed_link['query'][$key]);
      }

    // Set new params (overwrites any existing inherited params)
      foreach ($new_params as $key => $value) {
        $parsed_link['query'][$key] = $value;
      }

    // Glue link
      $link = self::implode_link($parsed_link);

    // Process catalog links
      if (empty($parsed_link['host']) || $parsed_link['host'] == preg_replace('#^([a-z|0-9|\.|-]+)(?:\:[0-9]+)?$#', '$1', $_SERVER['HTTP_HOST'])) {
        if ($parsed_link['path'] == WS_DIR_HTTP_HOME || !file_exists(FS_DIR_HTTP_ROOT . $parsed_link['path'])) {
          if (preg_match('#^'. WS_DIR_HTTP_HOME .'#', $parsed_link['path'])) {
            if (class_exists('route', false)) {
              if ($rewritten_link = route::rewrite($link, $language_code)) {
                $link = $rewritten_link;
              }
            }
          }
        }
      }

      return $link;
    }

    public static function full_link($link) {

      $parts = self::explode_link($link);
      $link = self::implode_link($parts);

      return $link;
    }

    public static function relpath($link) {
      $parts = self::explode_link($link);

      if (substr($parts['path'], 0, strlen(WS_DIR_HTTP_HOME)) == WS_DIR_HTTP_HOME) $parts['path'] = substr($parts['path'], strlen(WS_DIR_HTTP_HOME));

      return $parts['path'] . (!empty($parts['query']) ? '?'. http_build_query($parts['query'], '', '&') : '');
    }

    public static function fullpath($path) {

      if (substr($path, 0, 1) == '/') return $path;

      if (substr($path, 0, 4) == 'http') return parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);

      $dir = str_replace('\\', '/', dirname(parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH)));
      if (substr($dir, -1) != '/') $dir .= '/';

      $path = $dir . $path;

    // Relative path to absolute
      if (strpos($path, '..') !== false) {
        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
          if ('.' == $part) continue;
          if ('..' == $part) {
            array_pop($absolutes);
          } else {
            $absolutes[] = $part;
          }
        }
        $path = '/' . implode('/', $absolutes);
      }

    // Remove duplicate slashes
      while(strpos($path, '//') === true) str_replace('//', '/', $path);

      return $path;
    }

    public static function explode_link($link='') {

      $parts = parse_url($link);

      if (empty($parts['host'])) {
        $parts['scheme'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        @list($parts['host'], $parts['port']) = explode(':', $_SERVER['HTTP_HOST']);
        if (empty($parts['port'])) $parts['port'] = in_array($_SERVER['SERVER_PORT'], array('80', '443', '8080')) ? '' : $_SERVER['SERVER_PORT'];
      }

      if (empty($parts['scheme'])) $parts['scheme'] = 'http';
      if (!isset($parts['user'])) $parts['user'] = '';
      if (!isset($parts['pass'])) $parts['pass'] = '';
      if (!isset($parts['port'])) $parts['port'] = '';

      if (empty($parts['path'])) $parts['path'] = '/';

      if (!isset($parts['query'])) {
        $parts['query'] = array();
      } else {
        parse_str($parts['query'], $parsed_query);
        $parts['query'] = $parsed_query;
      }

      // Magic Quotes Fix
      if (ini_get('magic_quotes_gpc')) {
        if (!function_exists('stripslashes_recursive')) {
          function stripslashes_recursive($value) {
            if (is_array($value)) {
              $return = array();
              foreach ($value as $k => $v) {
                $return[$k] = stripslashes_recursive($v);
              }
            } else {
              $return = stripslashes($value);
            }
            return $return;
          }
        }
        $parts['query'] = stripslashes_recursive($parts['query']);
      }

      if (!isset($parts['fragment'])) $parts['fragment'] = '';

      return $parts;
    }

    public static function implode_link($parts) {

      if (empty($parts['host'])) {
        $parts['scheme'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        @list($parts['host'], $parts['port']) = explode(':', $_SERVER['HTTP_HOST']);
        if (empty($parts['port'])) $parts['port'] = in_array($_SERVER['SERVER_PORT'], array('80', '443', '8080')) ? '' : $_SERVER['SERVER_PORT'];
      }

      if (empty($parts['scheme'])) $parts['scheme'] = 'http';
      if (!isset($parts['user'])) $parts['user'] = '';
      if (!isset($parts['pass'])) $parts['pass'] = '';
      if (!isset($parts['port'])) $parts['port'] = '';

      if (empty($parts['path'])) $parts['path'] = '/';

      if (!isset($parts['query'])) {
        $parts['query'] = array();
      }

      if (!isset($parts['fragment'])) $parts['fragment'] = '';

      $link = $parts['scheme'] . '://'
            . (!empty($parts['user']) ? $parts['user'] . (!empty($parts['pass']) ? ':' . $parts['pass'] : '') .'@' : '')
            . (!empty($parts['host']) ? $parts['host'] : '')
            . (!empty($parts['port']) ? ':' . $parts['port'] : '')
            . $parts['path']
            . (!empty($parts['query']) ? '?' . http_build_query($parts['query'], '', '&') : '')
            . (!empty($parts['fragment']) ? '#' . $parts['fragment'] : '');

      return $link;
    }
  }

?>