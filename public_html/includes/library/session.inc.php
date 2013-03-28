<?php
  
  class session {
  
    public $data;
    
    private $system;
    
    public function __construct(&$system) {
      $this->system = &$system;
      
      if (!session_id()) {
        ini_set('session.use_cookies', 'On');
        ini_set('session.use_trans_sid', 'Off');
      
        session_set_cookie_params(0, '/');
        session_start();
      }
      
      $this->data = &$_SESSION[SESSION_UNIQUE_ID];
      
      if (empty($this->data['last_ip'])) $this->data['last_ip'] = $_SERVER['REMOTE_ADDR'];
      if (empty($this->data['last_agent'])) $this->data['last_agent'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
      
      $is_hijack = false;
      if ($this->data['last_ip'] != $_SERVER['REMOTE_ADDR']) $is_hijack = true;
      if (!empty($_SERVER['HTTP_USER_AGENT']) && $this->data['last_agent'] != $_SERVER['HTTP_USER_AGENT']) $is_hijack = true;
      
      if ($is_hijack) {
        session_regenerate_id(true);
        error_log('Session hijacking attempt from '. $_SERVER['REMOTE_ADDR'] .' on '. $_SERVER['REQUEST_URI'] .': Expecting '. $this->data['last_ip'] .' ['. $this->data['last_agent'] .'] while detected '. $_SERVER['REMOTE_ADDR'] .' ['. $_SERVER['HTTP_USER_AGENT'] .']');
        $this->reset();
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
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
    
    public function before_output() {
    }
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function reset() {
      session_destroy();
    }
    
  }
  
?>