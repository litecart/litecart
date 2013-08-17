<?php
  
  class lib_breadcrumbs {
    
    public $data = array();
    
    public function __construct() {
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    //public function startup() {
    //}
    
    public function before_capture() {
      $this->add($GLOBALS['system']->language->translate('title_home', 'Home'), WS_DIR_HTTP_HOME);
    }
    
    //public function after_capture() {
    //}
    
    public function prepare_output() {
    
      if (is_object($GLOBALS['system']->breadcrumbs)) {
        $breadcrumbs = '';
        $separator = '';
        foreach ($this->data as $breadcrumb) {
          $breadcrumbs .= $separator .'<li><a href="'. $breadcrumb['link'] .'">'. $breadcrumb['title'] .'</a></li>';
          $separator = ' &raquo; ';
        }
        $GLOBALS['system']->document->snippets['breadcrumbs'] = '<nav id="breadcrumbs">' . PHP_EOL
                                                         . '  <ul class="list-horizontal">' . PHP_EOL
                                                         . '    '. $breadcrumbs . PHP_EOL
                                                         . '  </ul>' . PHP_EOL
                                                         . '</nav>';
      }
    }
    
    //public function before_output() {
    //}
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function add($title, $link) {
      $this->data[] = array(
        'title' => $title,
        'link' => $link,
      );
    }
  }
  
?>