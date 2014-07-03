<?php

  class seo_links {
    
    private static $_cache = array();
    private static $_cache_id = '';
    private static $_classes = array();
    public static $enabled;
    
    public static function construct() {
    }
    
    //public static function load_dependencies() {
    //}
    
    //public static function initiate() {
    //}
    
    public static function startup() {
    
      self::$enabled = false;
      if (settings::get('seo_links_enabled')) {
        if (isset($_SERVER['HTTP_MOD_REWRITE'])) {
          self::$enabled = true;
        }
      }
      
      if (settings::get('cache_clear_seo_links')) {
        
        database::query(
          "delete from ". DB_TABLE_SEO_LINKS_CACHE .";"
        );
        
        database::query(
          "update ". DB_TABLE_SETTINGS ."
          set value = ''
          where `key` = 'cache_clear_seo_links'
          limit 1;"
        );
        
        notices::add('success', 'SEO links cache cleared');
        
      } else {
      
        if (self::$enabled) {
        // Import cached links
          self::$_cache_id = cache::cache_id('seo_links', array('language'));
          self::$_cache = cache::get(self::$_cache_id, 'file');
        }
      }
    }
    
    public static function before_capture() {
      
      if (!self::$enabled) return;
      
    // Set urls
      $base_link = link::get_base_link();
      $called_link = link::get_called_link();
      $seo_link = self::create_link($base_link);
      
    // If current url is not seo url
      if ($seo_link != '' && $called_link != $seo_link) {
        
        $redirect = true;
        
        if (!empty($_POST)) $redirect = false;
        
        if (defined('SEO_REDIRECT') && SEO_REDIRECT == false) $redirect = false;
        
        if (isset(notices::$data) && is_array(notices::$data)) {
          foreach (notices::$data as $notices) {
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
    
    //public static function after_capture() {
    //}
    
    //public static function prepare_output() {
    //}
    
    //public static function before_output() {
    //}
    
    public static function shutdown() {
      if (self::$enabled) {
        cache::set(self::$_cache_id, 'file', self::$_cache);
      }
    }
    
    ######################################################################
    
    private static function _load_class($class) {
      
      if (isset(self::$_classes[$class])) {
        if (is_object(self::$_classes[$class])) {
          return true;
        } else {
          return false;
        }
      }
      
      self::$_classes[$class] = null;

      if (!is_file(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'seo_links/url_' . $class .'.inc.php')) return false;
      
      $class_name = 'url_'.$class;
      self::$_classes[$class] = new $class_name();
      
      return true;
    }
    
    public static function link($link, $language_code) {
      
      $checksum = md5($link . $language_code);
      if (!empty(self::$_cache[$checksum])) return self::$_cache[$checksum];
      
      $seo_link = self::get_link($link, $language_code);
      if (!empty($seo_link)) {
        self::$_cache[$checksum] = $seo_link;
        return $seo_link;
      }
      
      $seo_link = self::create_link($link, $language_code);
      
      if (!empty($seo_link)) {
        self::$_cache[$checksum] = $seo_link;
        return $seo_link;
      }
      
      return $link;
    }
    
    public static function get_link($link='', $language_code='') {
      
      if (!self::$enabled) return;
      
      if (preg_match('/^'. preg_quote(WS_DIR_ADMIN, '/') .'/', parse_url($link, PHP_URL_PATH))) return;
      
      if (empty($link)) $link = link::get_called_link();
      
      if (empty($language_code)) $language_code = language::$selected['code'];
      
      $seo_cache_query = database::query(
        "select seo_uri from ". DB_TABLE_SEO_LINKS_CACHE ."
        where uri = '". database::input(link::relpath($link)) ."'
        and language_code = '". database::input($language_code) ."'
        limit 1;"
      );
      $seo_cache = database::fetch($seo_cache_query);
      
      return !empty($seo_cache['seo_uri']) ? link::full_link(WS_DIR_HTTP_HOME . $seo_cache['seo_uri']) : '';
    }
    
    public static function create_link($link, $language_code='') {
      
      if (!self::$enabled) return;
      
      if (empty($language_code)) $language_code = language::$selected['code'];
      
      if (!in_array($language_code, array_keys(language::$languages))) trigger_error('Invalid language code ('. $language_code .')', E_USER_ERROR);
      
      $parsed_link = link::parse_link($link);
      
      if ($parsed_link['host'] != $_SERVER['HTTP_HOST']) return $link;
      
    // Don't use seo for admin links
      if (substr($parsed_link['path'], 0, strlen(WS_DIR_ADMIN)) == WS_DIR_ADMIN) return $link;
      
    // Full webpath, if relative
      $parsed_link['path'] = link::fullpath($parsed_link['path']);
      
      if (substr($parsed_link['path'], -9) == 'index.php') $parsed_link['path'] = substr($parsed_link['path'], 0, -9);
      
    // Set home path
      if (settings::get('seo_links_language_prefix')) {
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
      if (!self::_load_class($class)) {
        $seo_link = $parsed_link;
        $seo_link['path'] = $http_home_dir . link::relpath($parsed_link['path']);
        return link::unparse_link($seo_link);
      }
      
    // Bake base link
      $base_link = $parsed_link;
      $base_link = link::unparse_link($parsed_link);
      
    // Bake SEO link (for database)
      $seo_link = self::$_classes[$class]->process($parsed_link, $language_code);
      if (empty($seo_link)) return $link;
      if (substr($seo_link['path'], 0, strlen(WS_DIR_HTTP_HOME))) {
        $seo_link['path'] = $http_home_dir . substr($seo_link['path'], strlen(WS_DIR_HTTP_HOME));
      }
      $seo_link = link::unparse_link($seo_link);
      
    // If cache is outdated
      if ($seo_link != self::get_link($link)) {
        $seo_cache_query = database::query(
          "select seo_uri from ". DB_TABLE_SEO_LINKS_CACHE ."
          where uri = '". database::input(link::relpath($base_link)) ."'
          and language_code = '". database::input(language::$selected['code']) ."'
          limit 1;"
        );
        if (database::num_rows($seo_cache_query) == 0) {
          database::query(
            "insert into ". DB_TABLE_SEO_LINKS_CACHE ."
            (uri, seo_uri, language_code, date_created, date_updated)
            values ('". database::input(link::relpath($base_link)) ."', '". database::input(link::relpath($seo_link)) ."', '". language::$selected['code'] ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
          );
        } else {
          database::query(
            "update ". DB_TABLE_SEO_LINKS_CACHE ."
            set seo_uri = '". database::input(link::relpath($seo_link)) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
            where uri = '". database::input(link::relpath($base_link)) ."'
            and language_code = '". database::input(language::$selected['code']) ."'
            limit 1;"
          );
        }
      }
      
      return $seo_link;
    }
  }
?>