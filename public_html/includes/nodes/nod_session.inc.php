<?php

  class session {

    public static $data;

    public static function init() {

      register_shutdown_function(['session', 'close']);

      if (!self::start()) {
        trigger_error('Failed to start a session', E_USER_WARNING);
      }

      self::$data = &$_SESSION;

      if (empty(self::$data['last_ip_address'])){
        self::$data['last_ip_address'] = $_SERVER['REMOTE_ADDR'];
      }

      if (empty(self::$data['last_user_agent'])){
        self::$data['last_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
      }

      if ($_SERVER['REMOTE_ADDR'] != self::$data['last_ip_address']
      || $_SERVER['HTTP_USER_AGENT'] != self::$data['last_user_agent']) {
        self::$data['last_ip_address'] = $_SERVER['REMOTE_ADDR'];
        self::$data['last_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        self::regenerate_id();
      }
    }

    public static function start() {
      return session_start();
    }

    public static function close() {
      return session_write_close();
    }

    public static function clear() {
      return session_unset();
    }

    public static function destroy() {
      session_unset();
      return session_destroy();
    }

    public static function get_id() {
      return session_id();
    }

    public static function regenerate_id() {
      return session_regenerate_id(true);
    }
  }
