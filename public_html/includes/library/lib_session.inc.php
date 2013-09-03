<?php
  
  class lib_session {
  
    public $data;
    
    public function __construct() {
      
      @ini_set('session.use_cookies', '1');
      @ini_set('session.use_only_cookies', '1');
      @ini_set('session.use_trans_sid', '0');
      
      if (!session_id()) {
        session_set_cookie_params(0, WS_DIR_HTTP_HOME);
        session_start();
      }
      
      $this->data = &$_SESSION[SESSION_UNIQUE_ID];
      
      if (empty($this->data['last_ip'])) $this->data['last_ip'] = $_SERVER['REMOTE_ADDR'];
      if (empty($this->data['last_agent'])) $this->data['last_agent'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
      
      if ($this->data['last_ip'] != $_SERVER['REMOTE_ADDR']) {
        $this->regenerate_id();
        
        if (!empty($_SERVER['HTTP_USER_AGENT']) && $this->data['last_agent'] != $_SERVER['HTTP_USER_AGENT']) { // Decreased session security due to mobile networks
          error_log('Session hijacking attempt from '. $_SERVER['REMOTE_ADDR'] .' ['. $_SERVER['HTTP_USER_AGENT'] .'] on '. $_SERVER['REQUEST_URI'] .': Expecting '. $this->data['last_ip'] .' ['. $this->data['last_agent'] .']');
          $this->reset();
          header('Location: ' . $_SERVER['REQUEST_URI']);
          exit;
        }
      }
    }
    
    //public function load_dependencies() {
    //}
    
    //public function startup() {
    //}
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    //public function before_output() {
    //}
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function reset() {
      session_unset();
      session_destroy();
    }
    
    public function regenerate_id() {
      session_regenerate_id(true);
    }
    
  }
  
?>