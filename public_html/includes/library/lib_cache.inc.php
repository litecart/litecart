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

      if (isset(self::$_data['cache_clear'])) unset(self::$_data['cache_clear']);
      if (isset(self::$_data['cache_clear_thumbnails'])) unset(self::$_data['cache_clear_thumbnails']);

      if (settings::get('cache_clear')) {
        self::clear_cache();

        database::query(
          "update ". DB_TABLE_SETTINGS ."
          set value = ''
          where `key` = 'cache_clear'
          limit 1;"
        );

        foreach(glob(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'vqmod/vqcache/*.php') as $file){
          if (is_file($file)) unlink($file);
        }

        if (user::check_login()) {
          notices::add('success', 'Cache cleared');
        }
      }

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

        if (user::check_login()) {
          notices::add('success', 'Image thumbnails cache cleared');
        }
      }
    }

    public static function before_capture() {}

    public static function after_capture() {}

    public static function prepare_output() {}

    public static function before_output() {}

    public static function shutdown() {}

    ######################################################################

    public static function get($cache_id, $type, $max_age=900, $force=false) {

      if (empty($force)) {
        if (empty(self::$enabled)) return null;

      // Don't return cache for Internet Explorer (It doesn't support HTTP_CACHE_CONTROL)
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) return null;

        if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
          if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'no-cache') !== false) return null;
          if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'max-age=0') !== false) return null;
        }
      }

      switch ($type) {

        case 'database': // Not supported yet
          return null;

        case 'file':
          $cache_file = FS_DIR_HTTP_ROOT . WS_DIR_CACHE . '_cache_'.$cache_id;
          if (file_exists($cache_file) && filemtime($cache_file) > strtotime('-'.$max_age .' seconds')) {
            if (!$force && filemtime($cache_file) < strtotime(settings::get('cache_system_breakpoint'))) return null;

            $data = @json_decode(file_get_contents($cache_file), true);

            if (strtolower(language::$selected['charset']) != 'utf-8') {
              mb_convert_variables(language::$selected['charset'], 'UTF-8', $data);
            }

            return $data;
          }
          return null;

        case 'session':
          if (isset(self::$_data[$cache_id]['mtime']) && self::$_data[$cache_id]['mtime'] > strtotime('-'.$max_age .' seconds')) {
            if (!$force && self::$_data[$cache_id]['mtime'] < strtotime(settings::get('cache_system_breakpoint'))) return null;
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

      switch ($type) {

        case 'database': // Not supported yet
          return false;

        case 'file':
          $cache_file = FS_DIR_HTTP_ROOT . WS_DIR_CACHE . '_cache_' . $cache_id;

          if (strtolower(language::$selected['charset']) != 'utf-8') {
            mb_convert_variables('UTF-8', language::$selected['charset'], $data);
          }

          return @file_put_contents($cache_file, json_encode($data));

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

    public static function clear_cache($keyword=null) {

    // Clear files
      if (!empty($_name)) {
        $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_CACHE .'_cache*_'. $keyword .'_*');
      } else {
        $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_CACHE .'_cache_*');
      }

      if ($files) foreach ($files as $file) unlink($file);

    // Set breakpoint (for session cache)
      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". date('Y-m-d H:i:s') ."'
        where `key` = 'cache_system_breakpoint'
        limit 1;"
      );
    }

    public static function cache_id($keyword, $dependencies=array()) {

      $hash_string = $keyword;

      if (!is_array($dependencies)) {
        $dependencies = array($dependencies);
      }

      $dependencies[] = 'site';

      $dependencies = array_unique($dependencies);
      sort($dependencies);

      foreach ($dependencies as $dependant) {
        switch ($dependant) {
          case 'basename':
            $hash_string .= $_SERVER['PHP_SELF'];
            break;
          case 'currency':
            $hash_string .= currency::$selected['code'];
            break;
          case 'customer':
            $hash_string .= serialize(customer::$data);
            break;
          case 'domain':
          case 'host':
            $hash_string .= $_SERVER['HTTP_HOST'];
            break;
          case 'get':
            $hash_string .= serialize($_GET);
            break;
          case 'language':
            $hash_string .= language::$selected['code'];
            break;
          case 'layout':
            $hash_string .= document::$layout;
            break;
          case 'login':
            $hash_string .= !empty(customer::$data['id']) ? '1' : '0';
            break;
          case 'prices':
            $hash_string .= !empty(customer::$data['display_prices_including_tax']) ? '1' : '0';
            $hash_string .= !empty(customer::$data['country_code']) ? customer::$data['country_code'] : '';
            $hash_string .= !empty(customer::$data['zone_code']) ? customer::$data['zone_code'] : '';
            break;
          case 'post':
            $hash_string .= serialize($_POST);
            break;
          case 'region':
            $hash_string .= customer::$data['country_code'] . customer::$data['zone_code'];
            break;
          case 'site':
            $hash_string .= document::link(WS_DIR_HTTP_HOME);
            break;
          case 'template':
            $hash_string .= document::$template;
            break;
          case 'uri':
          case 'url':
            $hash_string .= $_SERVER['REQUEST_URI'];
            break;
          default:
            if (is_array($dependant)) {
              $hash_string .= $dependant;
            }
            $hash_string .= $dependant;
            break;
        }
      }

      return $keyword .'_'. md5($hash_string);
    }

    /* This option is not affected by $enabled since new data is always recorded */
    public static function capture($cache_id, $type='session', $max_age=900, $force=false) {

      if (isset(self::$_recorders[$cache_id])) trigger_error('Cache recorder already initiated ('. $cache_id .')', E_USER_ERROR);

      $_data = self::get($cache_id, $type, $max_age, $force);

      if (!empty($_data)) {
        echo $_data;
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
