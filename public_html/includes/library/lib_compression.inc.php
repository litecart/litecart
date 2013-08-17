<?php

  class lib_compression {
    
    
    public function __construct() {
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
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
      
    // Initialize GZIP compression to reduce bandwidth.
      if (!headers_sent() && $GLOBALS['system']->settings->get('gzip_enabled') == 'true') {
        ob_start("ob_gzhandler");
      }
    }
    
    //public function shutdown() {
    //}
    
    ######################################################################
  }