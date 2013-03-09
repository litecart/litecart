<?php
  
  class language {
  
    private $system;
    
    public $selected = array();
    public $languages = array();
    private $cache = array();
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
    }
    
    public function initiate() {
      
    /*
    // Force language to domain (if regional domains)
      switch (substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], '.'))) {
        case '.com':
          $code =  'en';
          break;
        case '.de':
          $code =  'de';
        case '.dk':
          $code =  'da';
          break;
        case '.no':
          $code =  'nb';
          break;
        case '.se':
          $code =  'sv';
          break;
        default:
          $code = '';
          break;
      }
      if (in_array($code, array_keys($this->system->language->languages))) {
        $this->system->language->set($language_code);
      }
    */
      
    }
    
    public function startup() {
    }
    
    public function before_capture() {
    }
    
    public function after_capture() {
    }
    
    public function prepare_output() {
    }
    
    public function before_output() {
    }
    
    public function shutdown() {
    }
  }
  
?>