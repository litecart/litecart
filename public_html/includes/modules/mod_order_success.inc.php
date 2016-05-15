<?php

  class mod_order_success extends module {
    public $options;
    public $rows = array();

    public function __construct() {

      parent::set_type('order_success');

      $this->load();
    }

    public function process($order) {

      $output = '';

      if (empty($this->modules)) return;

      foreach ($this->modules as $module_id => $module) {
        if ($data = $module->process($order)) {
          $output .= $data;
        }
      }

      return $output;
    }

    public function run($method_name, $module_id) {
      if (method_exists($this->modules[$module_id], $method_name)) {
        return call_user_func_array(array($this->modules[$module_id], $method_name), array_slice(func_get_args(), 2));
      }
    }
  }

?>