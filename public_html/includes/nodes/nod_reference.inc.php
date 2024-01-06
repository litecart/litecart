<?php

  class reference {

    private static $_cache;

    public static function __callStatic($resource, $arguments) {

      $checksum = crc32(http_build_query($arguments));

      if (isset(self::$_cache[$resource][$checksum])) {
        return self::$_cache[$resource][$checksum];
      }

      if (isset(self::$_cache[$resource]) && count(self::$_cache[$resource]) >= 100) {
        array_shift(self::$_cache[$resource]); // Toss the oldest cached object
      }

      $component = null;
      if (preg_match('#^(ref|ent)_#', $resource, $matches)) {
        $component = $matches[1];
        $resource = preg_replace('#^'. preg_quote($component, '#') .'_(.*)$#', '$1', $resource);
      }

      switch(true) {
        case ($component == 'ref'):
        case (!$component && is_file('app://includes/references/ref_'.basename($resource).'.inc.php')):

          $class_name = 'ref_'.$resource;

          //self::$_cache[$resource][$checksum] = new $class_name(...$arguments); // As of PHP 5.6
          $reflect = new ReflectionClass($class_name);
          self::$_cache[$resource][$checksum] = $reflect->newInstanceArgs($arguments);

          if (method_exists(self::$_cache[$resource][$checksum], '__construct')) {
            call_user_func_array([self::$_cache[$resource][$checksum], '__construct'], $arguments);
          }

          return self::$_cache[$resource][$checksum];

        case ($component == 'ent'):
        case (!$component && is_file('app://includes/entities/ent_'.basename($resource).'.inc.php')):

          $class_name = 'ent_'.$resource;
          $object = new $class_name(fallback($arguments[0]));

          self::$_cache[$resource][$checksum] = (object)$object->data;

          return self::$_cache[$resource][$checksum];

        default:

          self::$_cache[$resource][$checksum] = null;
          trigger_error('Unsupported reference or entity ('.$resource.')', E_USER_ERROR);
      }
    }
  }
