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
      if (empty($this->data['last_agent'])) $this->data['last_agent'] = $_SERVER['HTTP_USER_AGENT'];
      
      if ($this->data['last_ip'] != $_SERVER['REMOTE_ADDR'] || $this->data['last_agent'] != $_SERVER['HTTP_USER_AGENT']) {
        session_regenerate_id();
        $this->reset();
        error_log('Session hijacking attempt from '. $_SERVER['REMOTE_ADDR'] .' on '. $_SERVER['REQUEST_URI']);
        $this->system->notices->add('warnings', $this->system->language->translate('warning_session_hijacking_attempt_blocked', 'Warning: Session hijacking attempt blocked.'));
        header('Location: ' . $this->system->document->link(WS_DIR_HTTP_HOME));
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