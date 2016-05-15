<?php

  class mod_order_action extends module {
    public $data;

    public function __construct() {

      parent::set_type('order_action');

      $this->load();
    }

    public function options() {

      $this->data['options'] = array();

      if (empty($this->modules)) return;

      foreach ($this->modules as $module) {

        $module_options = $module->options();

        if (empty($module_options['options'])) continue;

        $this->data['options'][$module->id] = $module_options;
        $this->data['options'][$module->id]['id'] = $module->id;
        $this->data['options'][$module->id]['options'] = array();

        foreach ($module_options['options'] as $option) {
          $this->data['options'][$module->id]['options'][$option['id']] = $option;
        }
      }

      return $this->data['options'];
    }

    public function run($method_name, $module_id) {
      if (method_exists($this->modules[$module_id], $method_name)) {
        return call_user_func_array(array($this->modules[$module_id], $method_name), array_slice(func_get_args(), 2));
      }
    }
  }

?>