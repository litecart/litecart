<?php

  class cache {

    private static $_recorders = [];
    private static $_data;
    public static $enabled = true;

    public static function init() {

      self::$enabled = settings::get('cache_enabled');

      if (!isset(session::$data['cache'])) {
        session::$data['cache'] = [];
      }

      self::$_data = &session::$data['cache'];

      if (settings::get('cache_clear')) {
        self::clear_cache();

        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set value = '0'
          where `key` = 'cache_clear'
          limit 1;"
        );

        if (administrator::check_login()) {
          notices::add('success', 'Cache cleared');
        }
      }

      if (settings::get('cache_clear_thumbnails')) {

        clearstatcache();

        foreach (glob(FS_DIR_STORAGE .'cache/*', GLOB_ONLYDIR) as $dir) {
          foreach (glob($dir.'/*.{a?png,avif,gif,jpg,svg,webp}', GLOB_BRACE) as $file) {
            unlink($file);
          }
        }

        foreach (glob(FS_DIR_STORAGE .'cache/*.{a?png,avif,gif,jpg,svg,webp}', GLOB_BRACE) as $file) {
          unlink($file);
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."settings
          set value = '0'
          where `key` = 'cache_clear_thumbnails'
          limit 1;"
        );

        self::clear_cache('settings');

        if (administrator::check_login()) {
          notices::add('success', 'Image thumbnails cache cleared');
        }
      }
    }

    ######################################################################

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

          case 'administrator':
            $hash_string .= administrator::$data['id'];
            break;

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
            $hash_string .= (isset(route::$request['endpoint']) && route::$request['endpoint'] == 'admin') ? 'backend' : 'frontend';
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

			if (!$force_cache && !self::$enabled) {
				return;
      }

			if (isset($_SERVER['HTTP_CACHE_CONTROL']) && preg_match('#no-cache|max-age=0#i', $_SERVER['HTTP_CACHE_CONTROL'])) {
				return;
      }

      $data = null;

      switch ($token['storage']) {

        case 'file':

          $cache_file = FS_DIR_STORAGE .'cache/'. substr($token['id'], 0, 2) .'/'. $token['id'] .'.cache';

					if (file_exists($cache_file) && filemtime($cache_file) > strtotime('-'.$max_age .' seconds')) {
						$data = @json_decode(file_get_contents($cache_file), true);
					}

					break;

        case 'memory':

          switch (true) {

            case (function_exists('apcu_fetch')):
							$data = apcu_fetch($_SERVER['HTTP_HOST'].':'.$token['id']);
							break;

            case (function_exists('apc_fetch')):
							$data = apc_fetch($_SERVER['HTTP_HOST'].':'.$token['id']);
							break;

            default:
              $token['storage'] = 'file';
              return self::get($token, $max_age, $force_cache);
							break;
          }

        case 'session':

          if (isset(self::$_data[$token['id']]['mtime']) && self::$_data[$token['id']]['mtime'] > strtotime('-'.$max_age .' seconds')) {
						$data = self::$_data[$token['id']]['data'];
          }

					break;

        default:

          trigger_error('Invalid cache storage ('. $token['storage'] .')', E_USER_WARNING);

					break;
      }

			return $data;
    }

    public static function set($token, $data) {

      if (!self::$enabled) return;

      if (!$data) return;

      switch ($token['storage']) {

        case 'file':

          $cache_file = 'storage://cache/'. substr($token['id'], 0, 2) .'/'. $token['id'] .'.cache';

          if (!is_dir(dirname($cache_file))) {
            if (!mkdir(dirname($cache_file))) {
              trigger_error('Could not create cache subfolder', E_USER_WARNING);
							$result = false;
							break;
            }
          }

					return file_put_contents($cache_file, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        case 'memory':

          switch (true) {
            case (function_exists('apcu_store')):
							$result = apcu_store($_SERVER['HTTP_HOST'].':'.$token['id'], $data, $token['ttl']);
							break;

            case (function_exists('apc_store')):
							$result = apc_store($_SERVER['HTTP_HOST'].':'.$token['id'], $data, $token['ttl']);
							break;

            default:
              $token['storage'] = 'file';
							$result = self::set($token, $data);
							break;
          }

        case 'session':

          self::$_data[$token['id']] = [
            'mtime' => time(),
            'data' => $data,
          ];

					$result = true;
					break;

        default:
          trigger_error('Invalid cache type ('. $token['storage'] .')', E_USER_WARNING);
					$result = false;
					break;
      }

			return $result;
    }

    // Output recorder (This option is not affected by self::$enabled as fresh data is always building up cache)
    public static function capture($token, $max_age=900, $force_cache=false) {

      if (isset(self::$_recorders[$token['id']])) {
        trigger_error('Cache recorder already initiated ('. $token['id'] .')', E_USER_ERROR);
      }

      if ($data = self::get($token, $max_age, $force_cache)) {
        echo $data;
        return false;
      }

      self::$_recorders[$token['id']] = [
        'id' => $token['id'],
        'storage' => $token['storage'],
      ];

      ob_start();

      return true;
    }

    public static function end_capture($token=[]) {

      if (empty($token['id'])) {
        $token['id'] = current(array_reverse(self::$_recorders));
      }

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

    public static function clear_cache($keyword='') {

    // Clear modifications
      if (!$keyword) {
        foreach (glob('storage://addons/.cache/*.php') as $file) {
          if (is_file($file)) unlink($file);
        }
      }

    // Clear files
      foreach (glob(FS_DIR_STORAGE .'cache/*', GLOB_ONLYDIR) as $dir) {
        $search = $keyword ? '/*_'.$keyword.'*.cache' : '/*.cache';
        foreach (glob($dir.$search) as $file) unlink($file);
      }

    // Clear memory
      if (function_exists('apcu_delete')) {
        if ($keyword) {
          $cached_keys = new APCUIterator('#^'. preg_quote($_SERVER['HTTP_HOST'], '#') .':.*'. preg_quote($keyword, '#') .'.*#', APC_ITER_KEY);
        } else {
          $cached_keys = new APCUIterator('#^'. preg_quote($_SERVER['HTTP_HOST'], '#') .':.*#', APC_ITER_KEY);
        }
        foreach ($cached_keys as $key) {
          apcu_delete($key);
        }
      }

      if (function_exists('apc_delete')) {
        if ($keyword) {
          $cached_keys = new APCIterator('user', '#^'. preg_quote($_SERVER['HTTP_HOST'], '#') .':.*'. preg_quote($keyword, '#') .'.*#', APC_ITER_KEY);
        } else {
          $cached_keys = new APCIterator('user', '#^'. preg_quote($_SERVER['HTTP_HOST'], '#') .':.*#', APC_ITER_KEY);
        }
        foreach ($cached_keys as $key) {
          apc_delete($key);
        }
      }

      if ($keyword) {
        foreach (array_keys(self::$_data) as $token_id) {
          if (strpos($keyword, $token_id) !== false) {
            unset(self::$_data[$token_id]);
          }
        }
      } else {
        self::$_data = [];
      }
    }
  }
