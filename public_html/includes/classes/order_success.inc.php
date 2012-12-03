<?php

  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'module.inc.php');

  class order_success extends module {
    public $options;
    public $rows = array();

    public function __construct() {
      
      parent::set_type('order_success');
      
      $this->load();
    }
    
    public function process() {
      global $order;
      
      $output = '';
      
      if (empty($this->modules)) return;
      
      foreach ($this->modules as $module_id => $module) {
        if ($data = $module->process()) {
          $output .= $data;
        }
      }
      
      return $output;
    }
    
    public function run($method_name, $module_id) {
      if (method_exists($this->modules[$module_id], $method_name)) {
        return $this->modules[$module_id]->$method_name();
      }
    }
  }

?>