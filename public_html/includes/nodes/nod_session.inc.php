<?php

  class session {

    public static $data;

    public static function init() {

      ini_set('session.name', 'LCSESSID');
      ini_set('session.use_cookies', 1);
      ini_set('session.use_only_cookies', 1);
      ini_set('session.use_strict_mode', 1);
      ini_set('session.use_trans_sid', 0);
      ini_set('session.cookie_httponly', 1);
      ini_set('session.cookie_lifetime', 0);
      ini_set('session.cookie_path', WS_DIR_APP);
      ini_set('session.cookie_samesite', 'Lax');
      ini_set('session.gc_maxlifetime', 1440);

      register_shutdown_function(['session', 'close']);

      if (!self::start()) trigger_error('Failed to start a session', E_USER_WARNING);

      self::$data = &$_SESSION;

      if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        $_SERVER['HTTP_USER_AGENT'] = '';
      }

      if (empty(self::$data['last_ip_address'])){
        self::$data['last_ip_address'] = $_SERVER['REMOTE_ADDR'];
      }

      if (empty(self::$data['last_user_agent'])){
        self::$data['last_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
      }

      if ((!empty(self::$data['last_ip_address']) && $_SERVER['REMOTE_ADDR'] != self::$data['last_ip_address'])
       || (!empty(self::$data['last_user_agent']) && $_SERVER['HTTP_USER_AGENT'] != self::$data['last_user_agent'])) {
        self::$data['last_ip_address'] = $_SERVER['REMOTE_ADDR'];
        self::$data['last_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        self::regenerate_id();
      }
    }

    ######################################################################

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
