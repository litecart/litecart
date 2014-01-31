<?php
  
  class cache {
    private static $_recorders = array();
    private static $_data;
    public static $enabled = true;
    
    public static function construct() {
    }
    
    //public static function load_dependencies() {
    //}
    
    //public static function initiate() {
    //}
    
    public static function startup() {
      
      self::$enabled = settings::get('cache_enabled') ? true : false;
      
      if (!isset(session::$data['cache'])) session::$data['cache'] = array();
      self::$_data = &session::$data['cache'];
      
      if (settings::get('cache_clear_thumbnails')) {
        $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_CACHE . '*');
        if (!empty($files)) foreach($files as $file) {
          if (in_array(pathinfo($file, PATHINFO_EXTENSION), array('jpg', 'jpeg', 'gif', 'png'))) unlink($file);
        }
        database::query(
          "update ". DB_TABLE_SETTINGS ."
          set value = ''
          where `key` = 'cache_clear_thumbnails'
          limit 1;"
        );
        notices::add('success', 'Image thumbnails cache cleared');
      }
    }
    
    public static function before_capture() {}
    
    public static function after_capture() {}
    
    public static function prepare_output() {}
    
    public static function before_output() {}
    
    public static function shutdown() {}
    
    ######################################################################
    
    public static function get($cache_id, $type, $max_age=900) {
      
      if (empty(self::$enabled)) return null;
      
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
            if (filemtime($cache_file) < strtotime(settings::get('cache_system_breakpoint'))) return null;
            return unserialize(file_get_contents($cache_file));
          }
          return null;
        
        case 'session':
          if (isset(self::$_data[$cache_id]['mtime']) && self::$_data[$cache_id]['mtime'] > strtotime('-'.$max_age .' seconds')) {
            if (self::$_data[$cache_id]['mtime'] < strtotime(settings::get('cache_system_breakpoint'))) return null;
            return self::$_data[$cache_id]['data'];
          }
          return null;
          
        case 'memory': // Not supported yet
          return null;
          
        default:
          trigger_error('Invalid cache type ('. $type .')', E_USER_ERROR);
      }
    }
    
    public static function set($cache_id, $type, $data) {
    
      if (empty(self::$enabled)) return false;
      
      switch ($type) {
      
        case 'database': // Not supported yet
          return false;
      
        case 'file':
          $cache_file = FS_DIR_HTTP_ROOT . WS_DIR_CACHE . '_cache_' . $cache_id;
          file_put_contents($cache_file, serialize($data));
          return true;
        
        case 'session':
          self::$_data[$cache_id] = array(
            'mtime' => time(),
            'data' => $data,
          );
          return true;
          
        case 'memory': // Not supported yet
          return false;
          
        default:
          trigger_error('Invalid cache type ('. $type .')', E_USER_ERROR);
      }
    }
    
    public static function set_breakpoint() {
      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". date('Y-m-d H:i:s') ."'
        where `key` = 'cache_system_breakpoint'
        limit 1;"
      );
    }
    
    public static function cache_id($name, $dependants=array()) {
      
      if (!is_array($dependants)) $dependants = array($dependants);
      
      $dependants_string = '';
      foreach ($dependants as $dependant) {
        switch ($dependant) {
          case 'currency':
            $dependants_string .= currency::$selected['code'];
            break;
          case 'customer':
            $dependants_string .= serialize(customer::$data);
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
            $dependants_string .= language::$selected['code'];
            break;
          case 'prices':
            $dependants_string .= !empty(customer::$data['display_prices_including_tax']) ? '1' : '0';
            $dependants_string .= !empty(customer::$data['country_code']) ? customer::$data['country_code'] : '';
            $dependants_string .= !empty(customer::$data['zone_code']) ? customer::$data['zone_code'] : '';
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
    
    /* This option is not affected by $enabled since new data is always recorded */
    public static function capture($cache_id, $type='session', $max_age=3600) {
      
      if (isset(self::$_recorders[$cache_id])) trigger_error('Cache recorder already initiated ('. $cache_id .')', E_USER_ERROR);
      
      $_data = self::get($cache_id, $type, $max_age);
      
      if (!empty($_data)) {
        echo '<!-- Begin: Cache \''. $cache_id .'\' -->' . PHP_EOL
           . $_data . PHP_EOL
           . '<!-- End: Cache \''. $cache_id .'\' -->' . PHP_EOL;
        return false;
      }
      
      self::$_recorders[$cache_id] = array(
        'id' => $cache_id,
        'type' => $type,
      );
      
      ob_start();
      
      return true;
    }
    
    /* This option is not affected by $enabled since new data is always recorded */
    public static function end_capture($cache_id=null) {
      
      if (empty($cache_id)) $cache_id = current(array_reverse(self::$_recorders));
      
      if (!isset(self::$_recorders[$cache_id])) {
        if ($_data === false) trigger_error('Could not end buffer recording as cache_id doesn\'t exist', E_USER_ERROR);
        return;
      }
      
      $_data = ob_get_flush();
      
      if ($_data === false) trigger_error('No active recording while trying to end buffer recorder', E_USER_ERROR);
      
      self::set($cache_id, self::$_recorders[$cache_id]['type'], $_data);
      
      unset(self::$_recorders[$cache_id]);
    }
  }
  
?>