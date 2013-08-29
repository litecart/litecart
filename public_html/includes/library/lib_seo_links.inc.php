<?php

  class lib_seo_links {
    
    private $_cache = array();
    private $_cache_id = '';
    public $enabled;
    
    public function __construct() {
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    public function startup() {
    
      $this->enabled = false;
      if ($GLOBALS['system']->settings->get('seo_links_enabled')) {
        if (isset($_SERVER['HTTP_MOD_REWRITE'])) {
          $this->enabled = true;
        }
      }
      
      if ($this->enabled) {
      // Import cached links
        $this->_cache_id = $GLOBALS['system']->cache->cache_id('links', array('language'));
        $this->_cache = $GLOBALS['system']->cache->get($this->_cache_id, 'file');
      }
    }
    
    public function before_capture() {
      
      if (!$this->enabled) return;
    
    // Set urls
      $base_link = $GLOBALS['system']->link->get_base_link();
      $called_link = $GLOBALS['system']->link->get_called_link();
      $seo_link = $this->create_link($base_link);
      
    // If current url is not seo url
      if ($seo_link != '' && $called_link != $seo_link) {
        
        $redirect = true;
        
        if (!empty($_POST)) $redirect = false;
        
        if (defined('SEO_REDIRECT') && SEO_REDIRECT == false) $redirect = false;
        
        if (isset($GLOBALS['system']->notices->data) && is_array($GLOBALS['system']->notices->data)) {
          foreach ($GLOBALS['system']->notices->data as $notices) {
            if (!empty($notices)) $redirect = false;
          }
        }
        
        if ($redirect) {
          header('HTTP/1.1 301 Moved Permanently');
          header('Location: '. $seo_link);
          exit;
        }
      }
    }
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    //public function before_output() {
    //}
    
    public function shutdown() {
      if ($this->enabled) {
        $GLOBALS['system']->cache->set($this->_cache_id, 'file', $this->_cache);
      }
    }
    
    ######################################################################
    
    private function _load_class($class) {
      
      if (isset($this->classes[$class])) {
        if (is_object($this->classes[$class])) {
          return true;
        } else {
          return false;
        }
      }
      
      $this->classes[$class] = null;

      if (!is_file(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'seo_links/url_' . $class .'.inc.php')) return false;
      
      $class_name = 'url_'.$class;
      $this->classes[$class] = new $class_name();
      
      return true;
    }
    
    public function link($link, $language_code) {
      
      $checksum = md5($link . $language_code);
      if (!empty($this->cache[$checksum])) return $this->cache[$checksum];
      
      $seo_link = $this->get_link($link, $language_code);
      if (!empty($seo_link)) {
        $this->cache[$checksum] = $seo_link;
        return $seo_link;
      }
      
      $seo_link = $this->create_link($link, $language_code);
      
      if (!empty($seo_link)) {
        $this->cache[$checksum] = $seo_link;
        return $seo_link;
      }
      
      return $link;
    }
    
    public function get_link($link='', $language_code='') {
      
      if (!$this->enabled) return;
      
      if (preg_match('/^'. preg_quote(WS_DIR_ADMIN, '/') .'/', parse_url($link, PHP_URL_PATH))) return;
      
      if (empty($link)) $link = $GLOBALS['system']->link->get_called_link();
      
      if (empty($language_code)) $language_code = $GLOBALS['system']->language->selected['code'];
      
      $seo_cache_query = $GLOBALS['system']->database->query(
        "select seo_uri from ". DB_TABLE_SEO_LINKS_CACHE ."
        where uri = '". $GLOBALS['system']->database->input($GLOBALS['system']->link->relpath($link)) ."'
        and language_code = '". $GLOBALS['system']->database->input($language_code) ."'
        limit 1;"
      );
      $seo_cache = $GLOBALS['system']->database->fetch($seo_cache_query);
      
      return !empty($seo_cache['seo_uri']) ? $GLOBALS['system']->link->full_link(WS_DIR_HTTP_HOME . $seo_cache['seo_uri']) : '';
    }
    
    public function create_link($link, $language_code='') {
      
      if (!$this->enabled) return;
      
      if (empty($language_code)) $language_code = $GLOBALS['system']->language->selected['code'];
      
      if (!in_array($language_code, array_keys($GLOBALS['system']->language->languages))) trigger_error('Invalid language code ('. $language_code .')', E_USER_ERROR);
      
      $parsed_link = $GLOBALS['system']->link->parse_link($link);
      
      if ($parsed_link['host'] != $_SERVER['HTTP_HOST']) return $link;
      
    // Don't use seo for admin links
      if (substr($parsed_link['path'], 0, strlen(WS_DIR_ADMIN)) == WS_DIR_ADMIN) return $link;
      
    // Full webpath, if relative
      $parsed_link['path'] = $GLOBALS['system']->link->fullpath($parsed_link['path']);
      
      if (substr($parsed_link['path'], -9) == 'index.php') $parsed_link['path'] = substr($parsed_link['path'], 0, -9);
      
    // Set home path
      if ($GLOBALS['system']->settings->get('seo_links_language_prefix')) {
        $http_home_dir = WS_DIR_HTTP_HOME . $language_code .'/';
      } else {
        $http_home_dir = WS_DIR_HTTP_HOME;
      }
      
    // Extract class from URL
      if (substr($parsed_link['path'], 0, strlen(WS_DIR_HTTP_HOME)) == WS_DIR_HTTP_HOME) {
        $class = substr($parsed_link['path'], strlen(WS_DIR_HTTP_HOME));
      } else {
        $class = $parsed_link['path'];
      }
      $class = str_replace('/', '_', $class);
      $class = substr($class, 0, strrpos($class, '.'));
      
    // No class, bake default link
      if (!$this->_load_class($class)) {
        $seo_link = $parsed_link;
        $seo_link['path'] = $http_home_dir . $GLOBALS['system']->link->relpath($parsed_link['path']);
        return $GLOBALS['system']->link->unparse_link($seo_link);
      }
      
    // Bake base link
      $base_link = $parsed_link;
      $base_link = $GLOBALS['system']->link->unparse_link($parsed_link);
      
    // Bake SEO link (for database)
      $seo_link = $this->classes[$class]->process($parsed_link, $language_code);
      if (empty($seo_link)) return $link;
      if (substr($seo_link['path'], 0, strlen(WS_DIR_HTTP_HOME))) {
        $seo_link['path'] = $http_home_dir . substr($seo_link['path'], strlen(WS_DIR_HTTP_HOME));
      }
      $seo_link = $GLOBALS['system']->link->unparse_link($seo_link);
      
    // If cache is outdated
      if ($seo_link != $this->get_link($link)) {
        $seo_cache_query = $GLOBALS['system']->database->query(
          "select seo_uri from ". DB_TABLE_SEO_LINKS_CACHE ."
          where uri = '". $GLOBALS['system']->database->input($GLOBALS['system']->link->relpath($base_link)) ."'
          and language_code = '". $GLOBALS['system']->database->input($GLOBALS['system']->language->selected['code']) ."'
          limit 1;"
        );
        if ($GLOBALS['system']->database->num_rows($seo_cache_query) == 0) {
          $GLOBALS['system']->database->query(
            "insert into ". DB_TABLE_SEO_LINKS_CACHE ."
            (uri, seo_uri, language_code, date_created, date_updated)
            values ('". $GLOBALS['system']->database->input($GLOBALS['system']->link->relpath($base_link)) ."', '". $GLOBALS['system']->database->input($GLOBALS['system']->link->relpath($seo_link)) ."', '". $GLOBALS['system']->language->selected['code'] ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
          );
        } else {
          $GLOBALS['system']->database->query(
            "update ". DB_TABLE_SEO_LINKS_CACHE ."
            set seo_uri = '". $GLOBALS['system']->database->input($GLOBALS['system']->link->relpath($seo_link)) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
            where uri = '". $GLOBALS['system']->database->input($GLOBALS['system']->link->relpath($base_link)) ."'
            and language_code = '". $GLOBALS['system']->database->input($GLOBALS['system']->language->selected['code']) ."'
            limit 1;"
          );
        }
      }
      
      return $seo_link;
    }
  }
?>