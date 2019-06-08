<?php

  class event {

    public static $_callbacks = array();
    public static $_fired_events = array();

    public static function register($event, $function) {

      $checksum = md5(json_encode($function));

      if (!empty(self::$_callbacks[$event][$checksum])) {
        trigger_error('Callback already registered', E_USER_WARNING);
        return;
      }

      self::$_callbacks[$event][$checksum] = $function;
    }

    public static function fire($event) {

      if (in_array($event, self::$_fired_events)) {
        trigger_error("Event already fired ($event)", E_USER_WARNING);
        return;
      }

      if (empty(self::$_callbacks[$event])) return;

      $args = array_slice(func_get_args(), 1);

      foreach (self::$_callbacks[$event] as $callback) {

        switch(true) {
          case (is_callable($callback)):
            $callback($args);
            break;

          default:
            call_user_func($callback, $args);
            break;
        }
      }

      self::$_fired_events[] = $event;
    }
  }
