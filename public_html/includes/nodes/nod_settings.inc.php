<?php

  class settings {
    private static $_cache;

    public static function init() {

      $settings_query = database::query(
        "select `key`, `value`, `function`
        from ". DB_TABLE_PREFIX ."settings
        where `type` = 'global';"
      );

      while ($setting = database::fetch($settings_query)) {

        if (substr($setting['function'], 0, 9) == 'regional_') {

          if (!class_exists('langauge', false) || empty(language::$selected)) continue;

          if ($setting['value']) {
            $setting['value'] = json_decode($setting['value'], true);

            if (isset($setting['value'][language::$selected['code']])) {
              $setting['value'] = $setting['value'][language::$selected['code']];

            } else if (isset($value['en'])) {
              $setting['value'] = $setting['value']['en'];

            } else {
              $setting['value'] = '';
            }

          } else {
            $setting['value'] = '';
          }
        }

        self::$_cache[$setting['key']] = $setting['value'];
      }

    // Check version
      if (settings::get('platform_database_version') != PLATFORM_VERSION) {
        trigger_error('Platform database version ('. settings::get('platform_database_version') .') does not match platform version ('. PLATFORM_VERSION .'). Did you run /install/upgrade.php?', E_USER_WARNING);
      }

    // Set time zone
      if ($timezone = self::get('store_timezone')) {
        date_default_timezone_set($timezone);
      }
    }

    ######################################################################

    public static function get(string $key, $fallback=null) {

      if (isset(self::$_cache[$key])) return self::$_cache[$key];

      $settings_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."settings
        where `key` = '". database::input($key) ."'
        limit 1;"
      );

      if (!database::num_rows($settings_query)) {
        if ($fallback === null) trigger_error('Unsupported settings key ('. $key .')', E_USER_WARNING);
        return $fallback;
      }

      while ($setting = database::fetch($settings_query)) {

        if (substr($setting['function'], 0, 9) == 'regional_') {

          if ($setting['value']) {
            $setting['value'] = json_decode($setting['value'], true);
          } else {
            $setting['value'] = [];
          }

          if (isset($setting['value'][language::$selected['code']])) {
            return self::$_cache[$key] = $setting['value'][language::$selected['code']];

          } else if (isset($value['en'])) {
            return self::$_cache[$key] = $setting['value']['en'];

          } else {
            return self::$_cache[$key] = '';
          }
        }

        return self::$_cache[$key] = $setting['value'];
        }
      }

    public static function set($key, $value) {
      self::$_cache[$key] = $value;
    }
  }
