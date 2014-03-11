<?php
  
  class mod_jobs extends module {
    public $data;
    public $cheapest = '';
    public $items = array();
    public $destination = array();

    public function __construct() {
      
      parent::set_type('jobs');
     
      $this->load();
    }
    
    public function process($module_id=null, $force=false) {
      global $order;
      
      $output = '';
      
      if (empty($this->modules)) return;
      
      if (!empty($module_id)) {
        echo $module_id . PHP_EOL;
        $this->modules[$module_id]->process($force);
      } else {
        foreach (array_keys($this->modules) as $module_id) {
          echo $module_id . PHP_EOL;
          $this->modules[$module_id]->process($force);
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