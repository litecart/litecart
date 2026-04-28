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
      ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && preg_match('#^on|1|true#i', $_SERVER['HTTPS']));
      ini_set('session.gc_maxlifetime', 1440);

      register_shutdown_function(['session', 'close']);

      if (!self::start()) trigger_error('Failed to start a session', E_USER_WARNING);

      self::$data = &$_SESSION;

      if (empty(self::$data['last_ip_address'])) {
        self::$data['last_ip_address'] = $_SERVER['REMOTE_ADDR'];
      }

      if (empty(self::$data['last_user_agent'])) {
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

      $session_name = session_name();

      // Validate session ID from cookie to prevent session fixation attacks throwing warnings
      if (!empty($_COOKIE[$session_name])) {
        $sid = $_COOKIE[$session_name];
        if (!preg_match('#^[-,a-zA-Z0-9]+$#', $sid)) {
          setcookie($session_name, '', time() - 3600, '/');
          session_id('');  // Force new ID
        }
      }

      $result = session_start();

      // Ensure session cookie is set for the current session (to prevent waiting for page reload)
      if (empty($_COOKIE[$session_name]) && !headers_sent()) {
        $_COOKIE[$session_name] = session_id();
      }

      return $result;
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

    public static function get_name() {
      return session_name();
    }

    public static function regenerate_id() {
      return session_regenerate_id(true);
    }

    public static function csrf_token() {
      if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
      return $_SESSION['csrf_token'];
    }

    public static function rotate_csrf_token() {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      return $_SESSION['csrf_token'];
    }
  }
