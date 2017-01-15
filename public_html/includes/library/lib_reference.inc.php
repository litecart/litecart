<?php

  class reference {
    private static $_cache;

    //public static function construct() {
    //}

    //public static function load_dependencies() {
    //}

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

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function __callStatic($resource, $arguments) {

      if (empty($arguments[0])) {
        trigger_error('Passed argument cannot be empty', E_USER_WARNING);
        return;
      }

      if (isset(self::$_cache[$resource]) && count(self::$_cache[$resource]) >= 100) {
        self::$_cache[$resource] = array();
      }

      $checksum = md5(json_encode($arguments));

      if (isset(self::$_cache[$resource][$checksum])) {
        return self::$_cache[$resource][$checksum];
      }

      $component = null;
      if (preg_match('#^(ref|ctrl)_#', $resource, $matches)) {
        $component = $matches[1];
      }

      switch(true) {
        case ($component == 'ref'):
        case (!$component && is_file(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . 'ref_'.basename($resource).'.inc.php')):

          $class_name = 'ref_'.$resource;

          $reflector = new ReflectionClass($class_name);
          self::$_cache[$resource][$checksum] = $reflector->newInstanceArgs($arguments);

          return self::$_cache[$resource][$checksum];

        case ($component == 'ctrl'):
        case (!$component && is_file(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'ctrl_'.basename($resource).'.inc.php')):

          $class_name = 'ctrl_'.$resource;
          $object = new $class_name($arguments[0]);

          self::$_cache[$resource][$checksum] = new StdClass;

          if (!empty($object->data['id'])) {
            foreach ($object->data as $key => $value) self::$_cache[$resource][$checksum]->$key = $value;
          }

          return self::$_cache[$resource][$checksum];

        default:

          self::$_cache[$resource][$checksum] = null;
          trigger_error('Unsupported data object ('.$resource.')', E_USER_ERROR);
      }
    }
  }

?>