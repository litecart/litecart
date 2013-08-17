<?php
  
  class jobs extends module {
    public $data;
    public $cheapest = '';
    public $items = array();
    public $destination = array();

    public function __construct($type='session') {
      
      parent::set_type('jobs');
     
      $this->load();
    }
    
    public function process($module_id=null) {
      global $order;
      
      $output = '';
      
      if (empty($this->modules)) return;
      
      if (!empty($module_id)) {
        $this->modules[$module_id]->process();
        echo $module_id . PHP_EOL;
      } else {
        foreach (array_keys($this->modules) as $module_id) {
          $this->modules[$module_id]->process();
          echo $module_id . PHP_EOL;
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