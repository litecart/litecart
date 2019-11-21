<?php

  class session {

    public static $data;

    public static function init() {

      @ini_set('session.name', 'LCSESSID');
      @ini_set('session.gc_maxlifetime', 65535);
      @ini_set('session.use_cookies', 1);
      @ini_set('session.use_only_cookies', 1);
      @ini_set('session.use_trans_sid', 0);
      @ini_set('session.cookie_lifetime', 0);
      @ini_set('session.cookie_path', WS_DIR_APP);

      register_shutdown_function(array('session', 'close'));

      if (!self::start()) trigger_error('Failed to start a session', E_USER_WARNING);

      self::$data = &$_SESSION;

      if (!isset($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT'] = '';
      if (empty(self::$data['last_ip'])) self::$data['last_ip'] = $_SERVER['REMOTE_ADDR'];
      if (empty(self::$data['last_agent'])) self::$data['last_agent'] = $_SERVER['HTTP_USER_AGENT'];
      if ($_SERVER['REMOTE_ADDR'] != self::$data['last_ip'] && $_SERVER['HTTP_USER_AGENT'] != self::$data['last_agent']) {
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
      $_SESSION = array();
      return true;
    }

    public static function destroy() {

      self::clear();

      if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        header('Set-Cookie: '. session_name() .'=; path='. WS_DIR_APP .'; expires=-1; SameSite=Strict');
      }

      return session_destroy();
    }

    public static function get_id() {
      return session_id();
    }

    public static function regenerate_id() {
      return session_regenerate_id(true);
    }
  }
