<?php

  class cache {

    private static $_recorders = [];
    private static $_data;
    public static $enabled = true;

    public static function init() {

      self::$enabled = settings::get('cache_enabled') ? true : false;

      if (!isset(session::$data['cache'])) session::$data['cache'] = [];
      self::$_data = &session::$data['cache'];

      if (settings::get('cache_clear')) {
        self::clear_cache();

        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set value = '0'
          where `key` = 'cache_clear'
          limit 1;"
        );

        if (user::check_login()) {
          notices::add('success', 'Cache cleared');
        }
      }

      if (settings::get('cache_clear_thumbnails')) {

        clearstatcache();

        foreach (glob(FS_DIR_STORAGE .'cache/*', GLOB_ONLYDIR) as $dir) {
          foreach (glob($dir.'/*.{avif,jpg,png,webp}', GLOB_BRACE) as $file) {
            unlink($file);
          }
        }

        foreach (glob(FS_DIR_STORAGE .'cache/*.{avif,jpg,png,webp}', GLOB_BRACE) as $file) {
          unlink($file);
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set value = '0'
          where `key` = 'cache_clear_thumbnails'
          limit 1;"
        );

        self::clear_cache('settings');

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

    public static function token($keyword, $dependencies=[], $storage='memory', $ttl=900) {

      if (!in_array($storage, ['file', 'memory', 'session'])) {
        trigger_error('The storage type is not supported ('. $storage .')', E_USER_WARNING);
        return;
      }

      $hash_string = $keyword;

      if (!is_array($dependencies)) {
        $dependencies = [$dependencies];
      }

      $dependencies[] = 'site';

      if (settings::get('avif_enabled') && isset($_SERVER['HTTP_ACCEPT']) && preg_match('#image/avif#', $_SERVER['HTTP_ACCEPT'])) {
        $dependencies[] = 'avif';
      }

      if (settings::get('webp_enabled') && isset($_SERVER['HTTP_ACCEPT']) && preg_match('#image/webp#', $_SERVER['HTTP_ACCEPT'])) {
        $dependencies[] = 'webp';
      }

      $dependencies = array_unique($dependencies);
      sort($dependencies);

      foreach ($dependencies as $dependency) {
        switch ($dependency) {

          case 'avif':
            if (isset($_SERVER['HTTP_ACCEPT']) && preg_match('#image/avif#', $_SERVER['HTTP_ACCEPT'])) {
              $hash_string .= 'avif';
            }
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
            if (!empty(route::$request)) {
              $hash_string .= preg_match('#^'. preg_quote(ltrim(WS_DIR_ADMIN, '/'), '#') .'.*#', route::$request) ? 'backend' : 'frontend';
            }
            break;

          case 'get':
            $hash_string .= json_encode($_GET, JSON_UNESCAPED_SLASHES);
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
            $hash_string .= currency::$selected['code'];
            $hash_string .= !empty(customer::$data['display_prices_including_tax']) ? '1' : '0';
            $hash_string .= !empty(customer::$data['country_code']) ? customer::$data['country_code'] : '';
            $hash_string .= !empty(customer::$data['zone_code']) ? customer::$data['zone_code'] : '';
            break;

          case 'post':
            $hash_string .= json_encode($_POST, JSON_UNESCAPED_SLASHES);
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
            $hash_string .= document::link();
            break;

          case 'user':
            $hash_string .= user::$data['id'];
            break;

          case 'webp':
            if (isset($_SERVER['HTTP_ACCEPT']) && preg_match('#image/webp#', $_SERVER['HTTP_ACCEPT'])) {
              $hash_string .= 'webp';
            }
            break;

          case 'webpath':
            $hash_string .= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            break;

          default:
            $hash_string .= is_array($dependency) ? implode(',', $dependency) : $dependency;
            break;
        }
      }

      return [
        'id' => md5($hash_string) .'_'. $keyword,
        'storage' => $storage,
        'ttl' => $ttl,
      ];
    }

    public static function get($token, $max_age=900, $force_cache=false) {

      if (is_string($token)) {
        trigger_error('Cache id has been deprecated and replaced by a token', E_USER_DEPRECATED);
        return;
      }

      if (empty($force_cache)) {
        if (empty(self::$enabled)) return;

        if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
          if (preg_match('#no-cache|max-age=0#i', $_SERVER['HTTP_CACHE_CONTROL'])) return;
        }
      }

      switch ($token['storage']) {

        case 'file':

          $cache_file = FS_DIR_STORAGE .'cache/'. substr($token['id'], 0, 2) .'/'. $token['id'] .'.cache';

          if (file_exists($cache_file) && filemtime($cache_file) > strtotime('-'.$max_age .' seconds')) {
            if (filemtime($cache_file) < strtotime(settings::get('cache_system_breakpoint'))) return;

            if (!$data = file_get_contents($cache_file)) return;
            if (!$data = json_decode($data, true)) return;

            if (strtolower(language::$selected['charset']) != 'utf-8') {
              $data = language::convert_characters($data, 'UTF-8', language::$selected['charset']);
            }

            return $data;
          }
          return;

        case 'memory':

          switch (true) {
            case (function_exists('apcu_fetch')):
              return apcu_fetch($_SERVER['HTTP_HOST'].':'.$token['id']);

            case (function_exists('apc_fetch')):
              return apc_fetch($_SERVER['HTTP_HOST'].':'.$token['id']);

            default:
              $token['storage'] = 'file';
              return self::get($token, $max_age, $force_cache);
          }

        case 'session':

          if (isset(self::$_data[$token['id']]['mtime']) && self::$_data[$token['id']]['mtime'] > strtotime('-'.$max_age .' seconds')) {
            if (self::$_data[$token['id']]['mtime'] < strtotime(settings::get('cache_system_breakpoint'))) return;
            return self::$_data[$token['id']]['data'];
          }

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

      if (empty($data)) return;

      switch ($token['storage']) {

        case 'file':

          $cache_file = FS_DIR_STORAGE .'cache/'. substr($token['id'], 0, 2) .'/'. $token['id'] .'.cache';

          if (!is_dir(dirname($cache_file))) {
            if (!mkdir(dirname($cache_file))) {
              trigger_error('Could not create cache subfolder', E_USER_WARNING);
              return false;
            }
          }

          if (strtolower(language::$selected['charset']) != 'utf-8') {
            $data = language::convert_characters($data, language::$selected['charset'], 'UTF-8');
          }

          return file_put_contents($cache_file, json_encode($data, JSON_UNESCAPED_SLASHES));

        case 'memory':

          switch (true) {
            case (function_exists('apcu_store')):
              return apcu_store($_SERVER['HTTP_HOST'].':'.$token['id'], $data, $token['ttl']);

            case (function_exists('apc_store')):
              return apc_store($_SERVER['HTTP_HOST'].':'.$token['id'], $data, $token['ttl']);

            default:
              $token['storage'] = 'file';
              return self::set($token, $data);
          }

        case 'session':

          self::$_data[$token['id']] = [
            'mtime' => time(),
            'data' => $data,
          ];

          return true;

        default:
          trigger_error('Invalid cache type ('. $token['storage'] .')', E_USER_WARNING);
          return;
      }
    }

    // Output recorder (This option is not affected by $enabled as fresh data is always recorded)
    public static function capture($token, $max_age=900, $force_cache=false) {

      if (is_string($token)) {
        trigger_error('Cache id has been deprecated and replaced by a token', E_USER_DEPRECATED);
        return;
      }

      if (isset(self::$_recorders[$token['id']])) {
        trigger_error('Cache recorder already initiated ('. $token['id'] .')', E_USER_ERROR);
      }

      $_data = self::get($token, $max_age, $force_cache);

      if (!empty($_data)) {
        echo $_data;
        return false;
      }

      self::$_recorders[$token['id']] = [
        'id' => $token['id'],
        'storage' => $token['storage'],
      ];

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

    // Clear vQmod
      if (empty($keyword)) {
        foreach (glob(FS_DIR_STORAGE . 'vmods/.cache/*.php') as $file) {
          if (is_file($file)) unlink($file);
        }
      }

    // Clear files
      foreach (glob(FS_DIR_STORAGE .'cache/*', GLOB_ONLYDIR) as $dir) {
        $search = !empty($keyword) ? '/*_'.$keyword.'*.cache' : '/*.cache';
        foreach (glob($dir.$search) as $file) unlink($file);
      }

    // Clear memory
      if (function_exists('apcu_delete')) {
        if (!empty($keyword)) {
          $cached_keys = new APCUIterator('#^'. preg_quote($_SERVER['HTTP_HOST'], '#') .':.*'. preg_quote($keyword, '#') .'.*#', APC_ITER_KEY);
        } else {
          $cached_keys = new APCUIterator('#^'. preg_quote($_SERVER['HTTP_HOST'], '#') .':.*#', APC_ITER_KEY);
        }
        foreach ($cached_keys as $key) {
          apcu_delete($key);
        }
      }

      if (function_exists('apc_delete')) {
        if (!empty($keyword)) {
          $cached_keys = new APCIterator('user', '#^'. preg_quote($_SERVER['HTTP_HOST'], '#') .':.*'. preg_quote($keyword, '#') .'.*#', APC_ITER_KEY);
        } else {
          $cached_keys = new APCIterator('user', '#^'. preg_quote($_SERVER['HTTP_HOST'], '#') .':.*#', APC_ITER_KEY);
        }
        foreach ($cached_keys as $key) {
          apc_delete($key);
        }
      }

    // Set breakpoint (for all session cache)
      database::query(
        "update ". DB_TABLE_PREFIX ."settings
        set value = '". date('Y-m-d H:i:s') ."'
        where `key` = 'cache_system_breakpoint'
        limit 1;"
      );
    }
  }
