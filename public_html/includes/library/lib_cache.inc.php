<?php
  
  class lib_cache {
    private $recorders = array();
    private $data;
    public $enabled = true;
    
    public function __construct() {
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    public function startup() {
      
      $this->enabled = $GLOBALS['system']->settings->get('cache_enabled') ? true : false;
      
      if (!isset($GLOBALS['system']->session->data['cache'])) $GLOBALS['system']->session->data['cache'] = array();
      $this->data = &$GLOBALS['system']->session->data['cache'];
      
      if ($GLOBALS['system']->settings->get('cache_clear_thumbnails')) {
        $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_CACHE . '*');
        if (!empty($files)) foreach($files as $file) {
          if (in_array(pathinfo($file, PATHINFO_EXTENSION), array('jpg', 'jpeg', 'gif', 'png'))) unlink($file);
        }
        $GLOBALS['system']->database->query(
          "update ". DB_TABLE_SETTINGS ."
          set value = ''
          where `key` = 'cache_clear_thumbnails'
          limit 1;"
        );
        $GLOBALS['system']->notices->add('success', 'Image thumbnails cache cleared');
      }
      
      if ($GLOBALS['system']->settings->get('cache_clear_seo_links')) {
        $GLOBALS['system']->database->query(
          "delete from ". DB_TABLE_SEO_LINKS_CACHE .";"
        );
        $GLOBALS['system']->database->query(
          "update ". DB_TABLE_SETTINGS ."
          set value = ''
          where `key` = 'cache_clear_seo_links'
          limit 1;"
        );
        $GLOBALS['system']->notices->add('success', 'SEO links cache cleared');
      }
    }
    
    public function before_capture() {}
    
    public function after_capture() {}
    
    public function prepare_output() {}
    
    public function before_output() {}
    
    public function shutdown() {}
    
    ######################################################################
    
    public function set_breakpoint() {
      $GLOBALS['system']->database->query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". date('Y-m-d H:i:s') ."'
        where `key` = 'cache_system_breakpoint'
        limit 1;"
      );
    }
    
    public function cache_id($name, $dependants=array()) {
      
      if (!is_array($dependants)) $dependants = array($dependants);
      
      $dependants_string = '';
      foreach ($dependants as $dependant) {
        switch ($dependant) {
          case 'currency':
            $dependants_string .= $GLOBALS['system']->currency->selected['code'];
            break;
          case 'customer':
            $dependants_string .= serialize($GLOBALS['system']->customer->data);
            break;
          case 'host':
            $dependants_string .= $_SERVER['HTTP_HOST'];
            break;
          case 'basename':
            $dependants_string .= $_SERVER['PHP_SELF'];
            break;
          case 'get':
            $dependants_string .= serialize($_GET);
            break;
          case 'post':
            $dependants_string .= serialize($_POST);
            break;
          case 'uri':
            $dependants_string .= $_SERVER['REQUEST_URI'];
            break;
          case 'language':
            $dependants_string .= $GLOBALS['system']->language->selected['code'];
            break;
          case 'prices':
            $dependants_string .= $GLOBALS['system']->settings->get('display_prices_including_tax');
            break;
          default:
            if (is_array($dependant)) {
              $dependants_string .= $dependant;
            }
            break;
        }
        $dependants_string .= $name;
      }
      
      return $name .'_'. md5($name . $dependants_string);
    }
    
    public function capture($cache_id, $type='session', $max_age=3600) {
      
      if (isset($this->recorders[$cache_id])) trigger_error('Cache recorder already initiated ('. $cache_id .')', E_USER_ERROR);
      
      $data = $this->get($cache_id, $type, $max_age);
      if (!empty($data)) {
        echo '<!-- Begin: Cache \''. $cache_id .'\' -->' . PHP_EOL
           . $data . PHP_EOL
           . '<!-- End: Cache \''. $cache_id .'\' -->' . PHP_EOL;
        return false;
      }
      
      $this->recorders[$cache_id] = array(
        'id' => $cache_id,
        'type' => $type,
      );
      ob_start();
      
      return true;
    }
    
    public function end_capture($cache_id=null) {
    
      if (empty($this->enabled)) return false;
    
      if (empty($cache_id)) $cache_id = current(array_reverse($this->recorders));
      
      if (!isset($this->recorders[$cache_id])) return false;
      
      $data = ob_get_flush();
      
      $this->set($cache_id, $this->recorders[$cache_id]['type'], $data);
      
      unset($this->recorders[$cache_id]);
    }
    
    public function get($cache_id, $type, $max_age=900) {
      
      if (empty($this->enabled)) return null;
      
    // Don't return cache for Internet Explorer (It doesn't support HTTP_CACHE_CONTROL)
      if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) return null;
      
      if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
        if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'no-cache') !== false) return null;
        if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'max-age=0') !== false) return null;
      }
    
      switch ($type) {
      
        case 'database': // Not supported yet
          return null;
      
        case 'file':
          $cache_file = FS_DIR_HTTP_ROOT . WS_DIR_CACHE . '_cache_'.$cache_id;
          if (file_exists($cache_file) && filemtime($cache_file) > strtotime('-'.$max_age .' seconds')) {
            if (filemtime($cache_file) < strtotime($GLOBALS['system']->settings->get('cache_system_breakpoint'))) return null;
            return unserialize(file_get_contents($cache_file));
          }
          return null;
        
        case 'session':
          if (isset($this->data[$cache_id]['mtime']) && $this->data[$cache_id]['mtime'] > strtotime('-'.$max_age .' seconds')) {
            if ($this->data[$cache_id]['mtime'] < strtotime($GLOBALS['system']->settings->get('cache_system_breakpoint'))) return null;
            return $this->data[$cache_id]['data'];
          }
          return null;
          
        case 'memory': // Not supported yet
          return null;
          
        default:
          trigger_error('Invalid cache type ('. $type .')', E_USER_ERROR);
      }
    }
    
    public function set($cache_id, $type, $data) {
    
      if (empty($this->enabled)) return false;
      
      switch ($type) {
      
        case 'database': // Not supported yet
          return false;
      
        case 'file':
          $cache_file = FS_DIR_HTTP_ROOT . WS_DIR_CACHE . '_cache_' . $cache_id;
          file_put_contents($cache_file, serialize($data));
          return true;
        
        case 'session':
          $this->data[$cache_id] = array(
            'mtime' => mktime(),
            'data' => $data,
          );
          return true;
          
        case 'memory': // Not supported yet
          return false;
          
        default:
          trigger_error('Invalid cache type ('. $type .')', E_USER_ERROR);
      }
    }
  }
  
?>