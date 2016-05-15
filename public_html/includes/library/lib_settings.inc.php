<?php

  class settings {
    private static $_cache;

    public static function construct() {
    }

    public static function load_dependencies() {

      $configuration_query = database::query(
        "select * from ". DB_TABLE_SETTINGS ."
        where `type` = 'global';"
      );
      while ($row = database::fetch($configuration_query)) {
        self::$_cache[$row['key']] = $row['value'];
      }

    // Set time zone
      date_default_timezone_set(self::get('store_timezone'));
    }

    //public static function initiate() {
    //}

    //public static function startup() {
    //}

    //public static function before_capture() {
    //}

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    public static function before_output() {
    }

    //public static function shutdown() {
    //}

    ######################################################################

    public static function get($key, $default=null) {

      if (isset(self::$_cache[$key])) return self::$_cache[$key];

      $configuration_query = database::query(
        "select * from ". DB_TABLE_SETTINGS ."
        where `key` = '". database::input($key) ."'
        limit 1;"
      );

      if (!database::num_rows($configuration_query)) {
        if ($default === null) trigger_error('Unsupported settings key ('. $key .')', E_USER_WARNING);
        return $default;
      }

      while ($row = database::fetch($configuration_query)) {
        self::$_cache[$key] = $row['value'];
      }

      return self::$_cache[$key];
    }

    public static function set($key, $value) {
      self::$_cache[$key] = $value;
    }
  }

?>