<?php

  class cache {
    private static $_recorders = array();
    private static $_data;
    public static $enabled = true;

    public static function init() {

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

        foreach (glob(FS_DIR_APP . 'vqmod/vqcache/*.php') as $file) {
          if (is_file($file)) unlink($file);
        }

        if (user::check_login()) {
          notices::add('success', 'Cache cleared');
        }
      }

      if (settings::get('cache_clear_thumbnails')) {
        $files = glob(FS_DIR_APP . 'cache/' . '*');

        if (!empty($files)) foreach ($files as $file) {
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

    ######################################################################

    public static function cache_id() {
      trigger_error('The method cache::cache_id() is deprecated, use instead cache::token()', E_USER_DEPRECATED);
      return;
    }

    public static function token($keyword, $dependencies=array(), $storage='file', $ttl=900) {

      $storage_types = array(
        //'database',
        'file',
        'session',
        //'memory',
      );

      if (!in_array($storage, $storage_types)) {
        trigger_error('The storage type is not supported ('. $storage .')', E_USER_WARNING);
        return;
      }

      $hash_string = $keyword;

      if (!is_array($dependencies)) {
        $dependencies = array($dependencies);
      }

      $dependencies[] = 'site';

      $dependencies = array_unique($dependencies);
      sort($dependencies);

      foreach ($dependencies as $dependency) {
        switch ($dependency) {

          case 'basename':
            $hash_string .= $_SERVER['PHP_SELF'];
            break;

          case 'country':
            $hash_string .= customer::$data['country_code'];
            break;

          case 'currency':
            $hash_string .= currency::$selected['code'];
            break;

          case 'customer':
            $hash_string .= customer::$data['id'];
            break;

          case 'domain':
          case 'host':
            $hash_string .= $_SERVER['HTTP_HOST'];
            break;

          case 'endpoint':
            $hash_string .= preg_match('#^'. preg_quote(ltrim(WS_DIR_ADMIN, '/'), '#') .'.*#', route::$request) ? 'backend' : 'frontend';
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
            $hash_string .= document::link(WS_DIR_APP);
            break;

          case 'template':
            $hash_string .= document::$template;
            break;

          case 'uri':
          case 'url':
            $hash_string .= $_SERVER['REQUEST_URI'];
            break;

          case 'user':
            $hash_string .= user::$data['id'];
            break;

          default:
            $hash_string .= is_array($dependency) ? implode('', $dependency) : $dependency;
            break;
        }
      }

      return array(
        'id' => $keyword .'_'. md5($hash_string),
        'storage' => $storage,
        'ttl' => $ttl,
      );
    }

    public static function get($token, $max_age=900, $force_cache=false) {

      if (is_string($token)) {
        trigger_error('Cache id has been deprecated and replaced by a token', E_USER_DEPRECATED);
        return;
      }

      if (empty($force_cache)) {
        if (empty(self::$enabled)) return;

      // Don't return cache for Internet Explorer (It doesn't support HTTP_CACHE_CONTROL)
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) return;

        if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
          if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'no-cache') !== false) return;
          if (strpos(strtolower($_SERVER['HTTP_CACHE_CONTROL']), 'max-age=0') !== false) return;
        }
      }

      switch ($token['storage']) {

        case 'database': // Reserved, but not implemented
          return;

        case 'file':
          $cache_file = FS_DIR_APP . 'cache/' . '_cache_'.$token['id'];
          if (file_exists($cache_file) && filemtime($cache_file) > strtotime('-'.$max_age .' seconds')) {
            if (filemtime($cache_file) < strtotime(settings::get('cache_system_breakpoint'))) return;

            $data = @json_decode(file_get_contents($cache_file), true);

            if (strtolower(language::$selected['charset']) != 'utf-8') {
              $data = language::convert_characters($data, 'UTF-8', language::$selected['charset']);
            }

            return $data;
          }
          return;

        case 'session':
          if (isset(self::$_data[$token['id']]['mtime']) && self::$_data[$token['id']]['mtime'] > strtotime('-'.$max_age .' seconds')) {
            if (self::$_data[$token['id']]['mtime'] < strtotime(settings::get('cache_system_breakpoint'))) return;
            return self::$_data[$token['id']]['data'];
          }
          return;

        case 'memory': // Reserved, but not implemented
          return;

        default:
          trigger_error('Invalid cache storage ('. $token['storage'] .')', E_USER_WARNING);
          return;
      }
    }

    public static function set($token, $data) {

      if (is_string($token)) {
        trigger_error('Cache id has been deprecated and replaced by a token', E_USER_DEPRECATED);
        return;
      }

      switch ($token['storage']) {

        case 'database': // Reserved, but not implemented
          return false;

        case 'file':
          $cache_file = FS_DIR_APP . 'cache/' . '_cache_' . $token['id'];

          if (strtolower(language::$selected['charset']) != 'utf-8') {
            $data = language::convert_characters($data, language::$selected['charset'], 'UTF-8');
          }

          return @file_put_contents($cache_file, json_encode($data, JSON_UNESCAPED_SLASHES));

        case 'session':
          self::$_data[$token['id']] = array(
            'mtime' => time(),
            'data' => $data,
          );
          return true;

        case 'memory': // Reserved, but not implemented
          return false;

        default:
          trigger_error('Invalid cache type ('. $storage .')', E_USER_WARNING);
          return;
      }
    }

    // Output recorder (This option is not affected by $enabled as fresh data is always recorded)
    public static function capture($token, $max_age=900, $force_cache=false) {

      if (is_string($token)) {
        trigger_error('Cache id has been deprecated and replaced by a token', E_USER_DEPRECATED);
        return;
      }

      if (isset(self::$_recorders[$token['id']])) trigger_error('Cache recorder already initiated ('. $token['id'] .')', E_USER_ERROR);

      $_data = self::get($token, $max_age, $force_cache);

      if (!empty($_data)) {
        echo $_data;
        return false;
      }

      self::$_recorders[$token['id']] = array(
        'id' => $token['id'],
        'storage' => $token['storage'],
      );

      ob_start();

      return true;
    }

    public static function end_capture($token=null) {

      if (is_string($token)) {
        trigger_error('Cache id has been deprecated and replaced by a token', E_USER_DEPRECATED);
        return false;
      }

      if (empty($token['id'])) $token['id'] = current(array_reverse(self::$_recorders));

      if (!isset(self::$_recorders[$token['id']])) {
        trigger_error('Could not end buffer recording as token id doesn\'t exist', E_USER_WARNING);
        return false;
      }

      $_data = ob_get_flush();

      if ($_data === false) {
        trigger_error('No active recording while trying to end buffer recorder', E_USER_WARNING);
        return false;
      }

      self::set($token, $_data);

      unset(self::$_recorders[$token['id']]);

      return true;
    }

    public static function clear_cache($keyword=null) {

    // Clear files
      if (!empty($keyword)) {
        $files = glob(FS_DIR_APP . 'cache/' .'_cache*_'. $keyword .'_*');
      } else {
        $files = glob(FS_DIR_APP . 'cache/' .'_cache_*');
      }

      if ($files) foreach ($files as $file) unlink($file);

    // Set breakpoint (for all session cache)
      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". date('Y-m-d H:i:s') ."'
        where `key` = 'cache_system_breakpoint'
        limit 1;"
      );
    }
  }