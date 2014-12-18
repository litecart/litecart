<?php
  
  class session {
  
    public static $data;
    
    public static function construct() {
      
      @ini_set('session.use_cookies', '1');
      @ini_set('session.use_only_cookies', '1');
      @ini_set('session.use_trans_sid', '0');
      
      if (!session_id()) {
        session_set_cookie_params(0, WS_DIR_HTTP_HOME);
        self::start();
      }
      
      self::$data = &$_SESSION[SESSION_UNIQUE_ID];
      
      if (empty(self::$data['last_ip'])) self::$data['last_ip'] = $_SERVER['REMOTE_ADDR'];
      if (empty(self::$data['last_agent'])) self::$data['last_agent'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
      
      if (self::$data['last_ip'] != $_SERVER['REMOTE_ADDR'] || self::$data['last_agent'] != $_SERVER['HTTP_USER_AGENT']) {
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
      
      $sn = session_name();
      if (isset($_COOKIE[$sn])) {
        $session_id = $_COOKIE[$sn];
      } else if (isset($_GET[$sn])) {
        $session_id = $_GET[$sn];
      } else {
        return session_start();
      }

     if (!preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $session_id)) {
        return false;
      }
      
      return session_start();
    }
    
    public static function reset() {
      session_unset();
      session_destroy();
    }
    
    public static function regenerate_id() {
      session_regenerate_id(true);
    }
    
  }
  
?>