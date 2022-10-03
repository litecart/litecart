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

          if ($setting['value']) {
            $values = json_decode($setting['value'], true);
          } else {
            $values = [];
          }

          if (isset($values[language::$selected['code']])) {
            self::$_cache[$setting['key']] = $values[language::$selected['code']];

          } else if (isset($value['en'])) {
            self::$_cache[$setting['key']] = $values['en'];

          } else {
            self::$_cache[$setting['key']] = '';
          }
        }
      }

    // Check version
      if (settings::get('platform_database_version') != PLATFORM_VERSION) {
        trigger_error('Platform database version ('. settings::get('platform_database_version') .') does not match platform version ('. PLATFORM_VERSION .'). Did you run /install/upgrade.php?', E_USER_WARNING);
      }

    // Set time zone
      date_default_timezone_set(self::get('store_timezone'));
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
            $values = json_decode($setting['value'], true);
          } else {
            $values = [];
          }

          if (isset($values[language::$selected['code']])) {
            return self::$_cache[$key] = $values[language::$selected['code']];

          } else if (isset($value['en'])) {
            return self::$_cache[$key] = $values['en'];

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
