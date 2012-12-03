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
    
    public function destroy() {
      session_destroy();
    }
    
  }
  
?>