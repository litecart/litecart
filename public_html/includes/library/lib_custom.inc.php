<?php
  
  class custom {
  
    
    public static $selected = array();
    public static $languages = array();
    private static $_cache = array();
    
    public static function construct() {
    }
    
    //public static function load_dependencies() {
    //}
    
    public static function initiate() {
      
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
      if (in_array($code, array_keys(language::$languages))) {
        language::set($language_code);
      }
    */
      
    }
    
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
  }
  
?>