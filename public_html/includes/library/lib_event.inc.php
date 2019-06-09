<?php

  class event {

    public static $_callbacks = array();
    public static $_fired_events = array();

    public static function register($event, $callback) {

      $checksum = md5(json_encode($callback));

      if (!empty(self::$_callbacks[$event][$checksum])) {
        trigger_error("Callback already registered ($event)", E_USER_WARNING);
        return;
      }

      if (in_array($event, self::$_fired_events)) {
        if (is_callable($callback)) {
          $callback();
        } else {
          call_user_func($callback);
        }
        return;
      }

      self::$_callbacks[$event][$checksum] = $callback;
    }

    public static function fire($event) {

      if (in_array($event, self::$_fired_events)) {
        trigger_error("Event already fired ($event)", E_USER_WARNING);
        return;
      }

      if (empty(self::$_callbacks[$event])) return;

      $args = array_slice(func_get_args(), 1);

      foreach (self::$_callbacks[$event] as $callback) {

        if (is_callable($callback)) {
          $callback($args);
        } else {
          call_user_func($callback, $args);
        }
      }

      self::$_fired_events[] = $event;
    }
  }
