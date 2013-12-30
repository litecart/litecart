<?php
  
  class breadcrumbs {
    
    public static $data = array();
    
    //public static function construct() {
    //}
    
    //public static function load_dependencies() {
    //}
    
    //public static function initiate() {
    //}
    
    //public static function startup() {
    //}
    
    public static function before_capture() {
      self::add(language::translate('title_home', 'Home'), WS_DIR_HTTP_HOME);
    }
    
    //public static function after_capture() {
    //}
    
    public static function prepare_output() {
    
      if (class_exists('breadcrumbs')) {
        $breadcrumbs = '';
        $separator = '';
        foreach (self::$data as $breadcrumb) {
          $breadcrumbs .= '<li>'. $separator .'<a href="'. $breadcrumb['link'] .'">'. $breadcrumb['title'] .'</a></li>';
          $separator = ' &raquo; ';
        }
        document::$snippets['breadcrumbs'] = '<nav id="breadcrumbs">' . PHP_EOL
                                           . '  <ul class="list-horizontal">' . PHP_EOL
                                           . '    '. $breadcrumbs . PHP_EOL
                                           . '  </ul>' . PHP_EOL
                                           . '</nav>';
      }
    }
    
    //public static function before_output() {
    //}
    
    //public static function shutdown() {
    //}
    
    ######################################################################
    
    public static function add($title, $link) {
      self::$data[] = array(
        'title' => $title,
        'link' => $link,
      );
    }
  }
  
?>