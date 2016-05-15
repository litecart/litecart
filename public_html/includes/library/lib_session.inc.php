<?php

  class session {

    public static $data;

    public static function construct() {

      @ini_set('session.name', 'LCSESSID');
      @ini_set('session.gc_maxlifetime', 65535);
      @ini_set('session.use_cookies', 1);
      @ini_set('session.use_only_cookies', 1);
      @ini_set('session.use_trans_sid', 0);
      @ini_set('session.cookie_lifetime', 0);
      @ini_set('session.cookie_path', WS_DIR_HTTP_HOME);

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

    //public static function load_dependencies() {
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
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
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

?>