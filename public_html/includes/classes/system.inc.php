<?php

// Set up the system object
  class system {
    private static $_loaded_modules = array();
    private static $_modules;

  // Autoload library modules
    public static function init() {

      foreach(glob(FS_DIR_HTTP_ROOT . WS_DIR_LIBRARY . 'lib_*.inc.php') as $file) {
        self::load($file);
      }
    }

  // Load library objects
    public static function load($file) {

      $module = preg_replace('/^lib_(.*)\.inc\.php$/', '$1', basename($file));

      if (in_array($module, self::$_loaded_modules)) {
        trigger_error("Module '$module' is already loaded", E_USER_WARNING);
        return;
      }

      require_once vmod::check($file);

      if (method_exists($module, 'construct')) {
        forward_static_call(array($module, 'construct'));
      }

      self::$_loaded_modules[] = $module;
    }

    public static function run($method_name) {
      foreach (self::$_loaded_modules as $library_module) {
        if (method_exists($library_module, $method_name)) {
          forward_static_call(array($library_module, $method_name));
        }
      }
    }

    public static function get_loaded_modules() {
      return self::$_loaded_modules;
    }
  }
