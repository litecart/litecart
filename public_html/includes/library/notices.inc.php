<?php
  
  class notices {
    public $data = array();
    
    private $system;
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
      $this->data = &$this->system->session->data['notices'];
    }
    
    //public function initiate() {
    //}
    
    //public function startup() {
    //}
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    public function prepare_output() {
    
      foreach(array('debugs', 'errors', 'notices', 'warnings', 'success') as $notice_type) {
        if ($this->system->notices->get($notice_type)) {
          $alerts[] = '  <div class="notice '. $notice_type .'">' . implode('</div>' . PHP_EOL . '  <div class="notice '. $notice_type .'">', $this->system->notices->dump($notice_type)) . '</div>' . PHP_EOL;
        }
      }
      
      $this->reset();
      
      if (isset($alerts)) {
        $this->system->document->snippets['alerts'] = '<div id="notices-wrapper">' . PHP_EOL
                                                    . '  <div id="notices">'. PHP_EOL . implode(PHP_EOL, $alerts) . '</div>' . PHP_EOL
                                                    . '</div>' . PHP_EOL;
        unset($alerts);
      }
    }
    
    public function before_output() {
    }
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function add($type, $msg, $key=false) {
      if ($key) $this->data[$type][$key] = $msg;
      else $this->data[$type][] = $msg;
    }
    
    public function remove($type, $key) {
      unset($this->data[$type][$key]);
    }
    
    public function get($type) {
      if (!isset($this->data[$type])) return false;
      return $this->data[$type];
    }
    
    public function reset($type=null) {
    
      if ($type) {
        $this->data[$type] = array();
        
      } else {
        if (!empty($this->data)) {
          foreach ($this->data as $type => $container) {
            $this->data[$type] = array();
          }
        }
      }
    }
    
    public function dump($type) {
      $stack = $this->data[$type];
      $this->data[$type] = array();
      return $stack;
    }
  }
  
?>