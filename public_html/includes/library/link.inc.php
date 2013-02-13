<?php

  class link {
    private $system;
    private $cache = array();
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    //public function load_dependencies() {
    //}
    
    public function startup() {
      
    // Import cached translations
      $this->cache_id = $this->system->cache->cache_id('links', array('language', 'basename'));
      $this->cache = $this->system->cache->get($this->cache_id, 'file');
    }
    
    //public function initiate() {
    //}
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    public function before_output() {
    }
    
    public function shutdown() {
      $this->system->cache->set($this->cache_id, 'file', $this->cache);
    }
    
    ######################################################################
    
    public function get_base_link() {
      $link = $_SERVER['PHP_SELF'] . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
      return $this->full_link($link);
    }
    
    public function get_called_link() {
      return $this->full_link($_SERVER['REQUEST_URI']);
    }
    
    public function build_link($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      
      if ($document === null) {
        $document = parse_url($this->get_base_link(), PHP_URL_PATH);
        $inherit_params = true;
      } else if ($document == '') {
        $document = parse_url($this->get_base_link(), PHP_URL_PATH);
      } else if (substr($document, 0, 4) == 'http' || strpos($document, '?') !== false) {
        $base_link = $this->parse_link($document);
      } else {
        $base_link = array(
          'path' => $this->fullpath($document),
          'query' => array(),
        );
      }
      
      if (empty($base_link['path'])) $base_link['path'] = $_SERVER['SCRIPT_NAME'];
      
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
      
      $link = $this->unparse_link($base_link);
      
      $checksum = md5($link);
      if (!empty($this->cache[$checksum])) return $this->cache[$checksum];
      
      if ($this->system->settings->get('seo_links_enabled') == 'true') {
        $seo_link = $this->system->seo_links->get_cached_link($link, $language_code);
        if (empty($seo_link)) $seo_link = $this->system->seo_links->create_link($link, $language_code);
      }
      
      $link = !empty($seo_link) ? $seo_link : $link;
      
      $this->cache[$checksum] = $link;
      
      return $link;
    }
    
    public function full_link($link) {
      
      $parts = $this->parse_link($link);
      $link = $this->unparse_link($parts);
      
      return $link;
    }
    
    public function relpath($link) {
      $parts = $this->parse_link($link);
      
      if (substr($parts['path'], 0, strlen(WS_DIR_HTTP_HOME)) == WS_DIR_HTTP_HOME) $parts['path'] = substr($parts['path'], strlen(WS_DIR_HTTP_HOME));
      
      return $parts['path'] . (!empty($parts['query']) ? '?'. http_build_query($parts['query']) : '');
    }
    
    public function fullpath($path) {
      
      if (substr($path, 0, 1) == '/') return $path;
      
      if (substr($path, 0, 4) == 'http') return parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
      
      $dir = dirname(parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH));
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
    
    public function parse_link($link='') {
      
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
            . (!empty($parts['query']) ? '?' . http_build_query($parts['query']) : '')
            . (!empty($parts['fragment']) ? '#' . $parts['fragment'] : '');
            
      return $link;
    }
  }

?>