<?php

  class seo_links {
    
    public $enabled = false;
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    public function startup() {
      if ($this->system->settings->get('seo_links_enabled') == 'true') $this->enabled = true;
    }
    
    public function before_capture() {
    
      if (!$this->enabled) return;
    
    // Set urls
      $base_link = $this->system->link->get_base_link();
      $called_link = $this->system->link->get_called_link();
      $seo_link = $this->create_link($base_link);
      
    // If current url is not seo url
      if ($called_link != $seo_link && empty($_POST)) {
      
        $redirect = true;
        
        if (defined('SEO_REDIRECT') && SEO_REDIRECT == false) $redirect = false;
        
        if (isset($this->system->notices->data) && is_array($this->system->notices->data)) {
          foreach ($this->system->notices->data as $notices) {
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
    
    public function before_output() {
    }
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    private function load_class($class) {
      
      if (isset($this->classes[$class])) {
        if (is_object($this->classes[$class])) {
          return true;
        } else {
          return false;
        }
      }
      
      $this->classes[$class] = null;
      
      $file = FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'seo_links/' . $class . '.inc.php';
      
      if (file_exists($file)) {
        require_once($file);
      } else {
        return false;
      }
      $class_name = 'seo_link_'.$class;
      $this->classes[$class] = new $class_name($this->system);
      
      return true;
    }
    
    public function get_cached_link($link='', $text='', $language_code='') {
    
      if (!$this->enabled) return '';
      
      if (empty($link)) $link = $this->system->link->get_called_link();
      
      if (empty($language_code)) $language_code = $this->system->language->selected['code'];
      
      $seo_cache_query = $this->system->database->query(
        "select seo_uri from ". DB_TABLE_SEO_LINKS_CACHE ."
        where uri = '". $this->system->database->input($this->system->link->relpath($link)) ."'
        and language_code = '". $this->system->database->input($language_code) ."'
        limit 1;"
      );
      $seo_cache = $this->system->database->fetch($seo_cache_query);
      
      return !empty($seo_cache['seo_uri']) ? $this->system->link->full_link(WS_DIR_HTTP_HOME . $seo_cache['seo_uri']) : '';
    }
    
    public function create_link($link='', $language_code='') {
      
      if (empty($link)) $link = $this->system->link->get_called_link();
      if (empty($language_code)) $language_code = $this->system->language->selected['code'];
      
      if ($this->system->settings->get('seo_links_language_prefix') == 'true') {
        $http_home_dir = WS_DIR_HTTP_HOME . $language_code .'/';
      } else {
        $http_home_dir = WS_DIR_HTTP_HOME;
      }
      
      if (!in_array($language_code, array_keys($this->system->language->languages))) trigger_error('Invalid language code ('. $language_code .')', E_USER_ERROR);
      
      $parsed_link = $this->system->link->parse_link($link);
      
      if ($parsed_link['host'] != $_SERVER['HTTP_HOST']) return $link;
      
    // Full webpath, if relative
      $parsed_link['path'] = $this->system->link->fullpath($parsed_link['path']);
      
      if (substr($parsed_link['path'], -9) == 'index.php') $parsed_link['path'] = substr($parsed_link['path'], 0, -9);
      
    // Extract class from URL
      if (substr($parsed_link['path'], 0, strlen(WS_DIR_HTTP_HOME)) == WS_DIR_HTTP_HOME) {
        $class = substr($parsed_link['path'], strlen(WS_DIR_HTTP_HOME));
      } else {
        $class = $parsed_link['path'];
      }
      $class = str_replace('/', '_', $class);
      $class = substr($class, 0, strrpos($class, '.'));
      
    // No class, bake default link
      if (!$this->load_class($class)) {
        $seo_link = $parsed_link;
        $seo_link['path'] = $http_home_dir . $this->system->link->relpath($parsed_link['path']);
        return $this->system->link->unparse_link($seo_link);
      }
      
    // Halt if insufficient parameters
      foreach ($this->classes[$class]->config['params'] as $param) {
        if (!isset($parsed_link['query'][$param])) return '';
      }
      
    // Bake base link
      $base_link = $parsed_link;
      $base_link['query'] = array();
      foreach ($this->classes[$class]->config['params'] as $param) {
        $base_link['query'][$param] = $parsed_link['query'][$param];
      }
      $base_link = $this->system->link->unparse_link($base_link);
      
    // Bake SEO link (for database)
      $seo_link = $parsed_link;
      $seo_link['path'] = $http_home_dir . $this->classes[$class]->config['seo_path'];
      $seo_link['path'] = str_replace('%title', $this->classes[$class]->title($parsed_link, $language_code), $seo_link['path']);
      $seo_link['query'] = array();
      foreach ($this->classes[$class]->config['params'] as $param) {
        $seo_link['path'] = str_replace('%'.$param, $parsed_link['query'][$param], $seo_link['path']);
      }
      $seo_link = $this->system->link->unparse_link($seo_link);
      
    // Bake full seo link (for output)
      $full_seo_link = $parsed_link;
      $full_seo_link['path'] = $http_home_dir . $this->classes[$class]->config['seo_path'];
      $full_seo_link['path'] = str_replace('%title', $this->classes[$class]->title($parsed_link, $language_code), $full_seo_link['path']);
      foreach ($this->classes[$class]->config['params'] as $param) {
        $full_seo_link['path'] = str_replace('%'.$param, $parsed_link['query'][$param], $full_seo_link['path']);
        unset($full_seo_link['query'][$param]);
      }
      $full_seo_link = $this->system->link->unparse_link($full_seo_link);
      
    // If cache is out of date
      if ($seo_link != $this->get_cached_link($link)) {
        $seo_cache_query = $this->system->database->query(
          "select seo_uri from ". DB_TABLE_SEO_LINKS_CACHE ."
          where uri = '". $this->system->database->input($this->system->link->relpath($base_link)) ."'
          and language_code = '". $this->system->database->input($this->system->language->selected['code']) ."'
          limit 1;"
        );
        if ($this->system->database->num_rows($seo_cache_query) == 0) {
          $this->system->database->query(
            "insert into ". DB_TABLE_SEO_LINKS_CACHE ."
            (uri, seo_uri, language_code, date_created, date_updated)
            values ('". $this->system->database->input($this->system->link->relpath($base_link)) ."', '". $this->system->database->input($this->system->link->relpath($seo_link)) ."', '". $this->system->language->selected['code'] ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
          );
        } else {
          $this->system->database->query(
            "update ". DB_TABLE_SEO_LINKS_CACHE ."
            set seo_uri = '". $this->system->database->input($this->system->link->relpath($seo_link)) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
            where uri = '". $this->system->database->input($this->system->link->relpath($base_link)) ."'
            and language_code = '". $this->system->database->input($this->system->language->selected['code']) ."'
            limit 1;"
          );
        }
      }
      
      return $full_seo_link;
    }
    
    public function url_friendly_string($text) {
    
    // Remove <tags>
      $text = strip_tags($text);
      
    // Decode special characters
      $text = htmlspecialchars_decode($text, ENT_QUOTES);
      
    // Remove system characters []
      $text = preg_replace("/\[.*\]/U", "", $text);
      
    // Convert foreign characters
      $text = htmlentities($text, ENT_COMPAT, $this->system->language->selected['charset']);
      $text = preg_replace('/&(.*)(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '$1', $text);
      
    // Keep a-z0-9 and convert symbols to -
      $text = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $text);
      $text = preg_replace(array('/[^a-z0-9]/i', '/[-]+/'), '-', $text);
      
    // Leave no trailing -
      $text = strtolower(trim($text, '-'));
      return $text;
    }
  }
?>