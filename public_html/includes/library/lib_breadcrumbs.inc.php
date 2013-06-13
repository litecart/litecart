<?php
  
  class lib_breadcrumbs {
    
    private $system;
    public $data = array();
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    //public function startup() {
    //}
    
    public function before_capture() {
      $this->add($this->system->language->translate('title_home', 'Home'), WS_DIR_HTTP_HOME);
    }
    
    //public function after_capture() {
    //}
    
    public function prepare_output() {
    
      if (is_object($this->system->breadcrumbs)) {
        $breadcrumbs = '';
        $separator = '';
        foreach ($this->data as $breadcrumb) {
          $breadcrumbs .= $separator .'<li><a href="'. $breadcrumb['link'] .'">'. $breadcrumb['title'] .'</a></li>';
          $separator = ' &raquo; ';
        }
        $this->system->document->snippets['breadcrumbs'] = '<nav id="breadcrumbs">' . PHP_EOL
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