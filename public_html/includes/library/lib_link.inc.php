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
    
    public static function get_base_link() {
      $link = $_SERVER['SCRIPT_NAME'] . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
      
      return self::full_link($link);
    }
    
    public static function get_called_link() {
      return self::full_link($_SERVER['REQUEST_URI']);
    }
    
    public static function create_link($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      
      if ($document === null) {
        $document = parse_url(self::get_base_link(), PHP_URL_PATH);
        $inherit_params = true;
      } else if ($document == '') {
        $document = parse_url(self::get_base_link(), PHP_URL_PATH);
      } else if (substr($document, 0, 4) == 'http' || strpos($document, '?') !== false) {
        $base_link = self::parse_link($document);
      } else {
        $base_link = array(
          'path' => self::fullpath($document),
          'query' => array(),
        );
      }
      
      if (empty($base_link['path'])) $base_link['path'] = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
      while (strpos($base_link['path'], '//')) $base_link['path'] = str_replace('//', '/', $base_link['path']);
      
      if ($inherit_params === true) {
        $base_link['query'] = $_GET;
      }
      
      if (is_array($inherit_params)) {
        foreach ($_GET as $key => $value) {
          if (in_array($key, $inherit_params)) {
            $base_link['query'][$key] = $value;
          }
        }
      }
      
      if (is_string($skip_params)) $skip_params = array($skip_params);
      foreach ($skip_params as $key) {
        if (isset($base_link['query'][$key])) unset($base_link['query'][$key]);
      }
      
      foreach ($new_params as $key => $value) {
        $base_link['query'][$key] = $value;
      }
      
      $link = self::unparse_link($base_link);
      
      if (!empty(seo_links::$enabled)) {
        $seo_link = seo_links::$link($link, $language_code);
      }
      
      $link = !empty($seo_link) ? $seo_link : $link;
      
      return $link;
    }
    
    public static function full_link($link) {
      
      $parts = self::parse_link($link);
      $link = self::unparse_link($parts);
      
      return $link;
    }
    
    public static function relpath($link) {
      $parts = self::parse_link($link);
      
      if (substr($parts['path'], 0, strlen(WS_DIR_HTTP_HOME)) == WS_DIR_HTTP_HOME) $parts['path'] = substr($parts['path'], strlen(WS_DIR_HTTP_HOME));
      
      return $parts['path'] . (!empty($parts['query']) ? '?'. http_build_query($parts['query'], '', '&') : '');
    }
    
    public static function fullpath($path) {
      
      if (substr($path, 0, 1) == '/') return $path;
      
      if (substr($path, 0, 4) == 'http') return parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
      
      $dir = str_replace('\\', '/', dirname(parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH)));
      if (substr($dir, -1) != '/') $dir .= '/';
      
      $path = $dir . $path;
     
    // relative path to absolute
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
      
    // remove duplicate slashes
      while(strpos($path, '//') === true) str_replace('//', '/', $path);
      
      return $path;
    }
    
    public static function parse_link($link='') {
      
      $parts = parse_url($link);
      
      if (empty($parts['host'])) {
        $parts['scheme'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $parts['host'] = $_SERVER['HTTP_HOST'];
        $parts['port'] = !in_array($_SERVER['SERVER_PORT'], array('80', '443')) ? $_SERVER['SERVER_PORT'] : '';
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
    
    function unparse_link($parts=array()) {
      
      if (empty($parts['host'])) {
        $parts['scheme'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $parts['host'] = $_SERVER['HTTP_HOST'];
        $parts['port'] = !in_array($_SERVER['SERVER_PORT'], array('80', '443')) ? $_SERVER['SERVER_PORT'] : '';
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