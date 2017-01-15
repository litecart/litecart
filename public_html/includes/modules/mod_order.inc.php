<?php

  class mod_order extends module {
    public $actions = array();

    public function __construct() {
      $this->load('order');
    }

    public function actions() {

      $this->actions = array();

      if (empty($this->modules)) return;

      foreach ($this->modules as $module) {

        $actions = $module->actions();

        if (empty($actions)) continue;

        $this->data['actions'][$module->id] = $actions;
        $this->data['actions'][$module->id]['id'] = $module->id;
        $this->data['actions'][$module->id]['actions'] = array();

        foreach ($actions as $option) {
          $this->data['actions'][$module->id]['actions'][$option['id']] = $option;
        }
      }

      return $this->data['actions'];
    }

    public function validate($order) {

      if (empty($this->modules)) return;

      foreach ($this->modules as $module_id => $module) {
        if (method_exists($this->modules[$module_id], $method_name)) {
          if ($result = $module->validate($order)) return $result;
        }
      }
    }

    public function before_process($order) {

      if (empty($this->modules)) return;

      foreach ($this->modules as $module_id => $module) {
        if (method_exists($this->modules[$module_id], $method_name)) $module->before_process($order);
      }
    }

    public function after_process($order) {

      if (empty($this->modules)) return;

      foreach ($this->modules as $module_id => $module) {
        if (method_exists($this->modules[$module_id], $method_name)) $module->after_process($order);
      }
    }

    public function success($order) {

      if (empty($this->modules)) return;

      $output = '';

      foreach ($this->modules as $module_id => $module) {
        if (method_exists($this->modules[$module_id], $method_name)) {
          if ($data = $module->after_process($order)) {
            $output .= $data;
          }
        }
      }

      return $output;
    }

    public function run($method_name, $module_id) {

      if (empty($this->modules)) return;

      foreach ($this->modules as $module_id => $module) {
        if (method_exists($this->modules[$module_id], $method_name)) {
          call_user_func_array(array($this->modules[$module_id], $method_name), array_slice(func_get_args(), 2));
        }
      }
    }
  }

?>