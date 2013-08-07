<?php

  class order_total extends module {
    public $options;
    public $rows = array();

    public function __construct() {
      
      parent::set_type('order_total');
      
      $this->load();
    }
    
    public function process() {
      global $order;
      
      if (empty($this->modules)) return;
      
      foreach ($this->modules as $module_id => $module) {
        if ($rows = $module->process()) {
          foreach ($rows as $row) {
            $order->add_ot_row(array(
              'id' => $module_id,
              'title' => $row['title'],
              'value' => $row['value'],
              'tax' => $row['tax'],
              'calculate' => !empty($row['calculate']) ? 1 : 0,
            ));
          }
        }
      }
    }
    
    public function run($method_name, $module_id) {
      if (method_exists($this->modules[$module_id], $method_name)) {
        return $this->modules[$module_id]->$method_name();
      }
    }
  }

?>