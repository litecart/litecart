<?php
  
  class lib_form {
    
    public function __construct() {
    }
    
    public function load_dependencies() {
      
    // Check post token
      if (!empty($_POST) && (!defined('REQUIRE_POST_TOKEN') || REQUIRE_POST_TOKEN != false)) {
        if (!isset($_POST['token']) || $_POST['token'] != $this->session_post_token()) {
          error_log('Warning: Blocked a potential CSRF hacking attempt by '. $_SERVER['REMOTE_ADDR'] .' ['. (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') .'] requesting '. $_SERVER['REQUEST_URI'] .'.');
          $GLOBALS['system']->session->reset();
          header('HTTP/1.1 400 Bad Request');
          die('HTTP POST Error');
        }
      }
    }
    
    //public function initiate() {
    //}
    
    public function startup() {
      
    // Is there incoming ajax data that needs decoding?
      if (!empty($_POST) && strtolower($GLOBALS['system']->language->selected['charset']) != 'utf-8') {
        $flag_unicoded = false;
        
        if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'charset=utf-8') !== false) $flag_unicoded = true;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'charset') === false) $flag_unicoded = true;
        
        if ($flag_unicoded) {
          function utf8_decode_recursive($input) {
            $return = array();
            foreach ($input as $key => $val) {
              if (is_array($val)) $return[$key] = utf8_decode_recursive($val);
              else $return[$key] = utf8_decode($val);
            }
            return $return;          
          }
          $_POST = utf8_decode_recursive($_POST);
        }
      }
    }
    
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
    
    public function session_post_token() {
      return sha1(SESSION_UNIQUE_ID .':'. session_id());
    }
  }
  
?>