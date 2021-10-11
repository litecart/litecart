<?php

  class mod_order extends abs_module {

    public function __construct() {
      $this->load();
    }

    public function actions() {

      $actions = [];

      if (empty($this->modules)) return;

      foreach ($this->modules as $module) {

        if (!method_exists($module, 'actions')) continue;

        $result = $module->actions();

        if (empty($result)) continue;

        $actions[$module->id] = [
          'id' => $result['id'],
          'name' => $result['name'],
          'description' => @$result['description'],
          'actions' => [],
        ];

        foreach ($result['actions'] as $action) {
          $actions[$module->id]['actions'][$action['id']] = [
            'id' => $action['id'],
            'title' => $action['title'],
            'description' => @$action['description'],
            'function' => $action['function'],
            'target' => !empty($action['target']) ? $action['target'] : '_self',
          ];
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

    public function update($order) {
      return $this->run('update', null, $order);
    }

    public function delete($order) {
      return $this->run('delete', null, $order);
    }

    public function run($method_name, $module_id=null) {

      if (empty($this->modules)) return;

      if (!empty($module_id)) {
        $module_ids = [$module_id];
      } else {
        $module_ids = array_keys($this->modules);
      }

      foreach ($module_ids as $module_id) {
        if (method_exists($this->modules[$module_id], $method_name)) {
          call_user_func_array([$this->modules[$module_id], $method_name], array_slice(func_get_args(), 2));
        }
      }
    }
  }
