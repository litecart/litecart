<?php

  class mod_order extends module {

    public function __construct() {
      $this->load('order');
    }

    public function actions() {

      $actions = array();

      if (empty($this->modules)) return;

      foreach ($this->modules as $module) {

        if (!method_exists($module, 'actions')) continue;

        $result = $module->actions();

        if (empty($result)) continue;

        $actions[$module->id] = array(
          'id' => $result['id'],
          'name' => $result['name'],
          'description' => @$result['description'],
          'actions' => array(),
        );

        foreach ($result['actions'] as $action) {
          $actions[$module->id]['actions'][$action['id']] = array(
            'id' => $action['id'],
            'title' => $action['title'],
            'description' => @$action['description'],
            'function' => $action['function'],
          );
        }
      }

      return $actions;
    }

    public function validate($order) {

      if (empty($this->modules)) return;

      foreach ($this->modules as $module_id => $module) {
        if (method_exists($this->modules[$module_id], 'validate')) {
          if ($result = $module->validate($order)) return $result;
        }
      }
    }

    public function before_process($order) {

      if (empty($this->modules)) return;

      foreach ($this->modules as $module_id => $module) {
        if (method_exists($this->modules[$module_id], 'before_process')) $module->before_process($order);
      }
    }

    public function after_process($order) {

      if (empty($this->modules)) return;

      foreach ($this->modules as $module_id => $module) {
        if (method_exists($this->modules[$module_id], 'after_process')) $module->after_process($order);
      }
    }

    public function success($order) {

      if (empty($this->modules)) return;

      $output = '';

      foreach ($this->modules as $module_id => $module) {
        if (method_exists($this->modules[$module_id], 'success')) {
          if ($data = $module->success($order)) {
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
