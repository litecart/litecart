<?php

  class mod_customer extends abs_module {
    private $_cache;

    public function __construct() {

      $this->_cache = &session::$data['get_address_cache'];

      $this->load();
    }

    public function get_address($fields) {

      if (empty($this->modules)) return false;

      $checksum = crc32(http_build_query($fields));

      if (isset($this->_cache[$checksum])) {
        return $this->_cache[$checksum];
      }

      $this->_cache[$checksum] = [];

      foreach ($this->modules as $module) {

        if (!method_exists($module, 'get_address')) continue;

        if ($result = $module->get_address($fields)) {
          if (is_array($result) && empty($result['error'])) {
            foreach ($result as $key => $value) {
              if (!empty($result[$key])) $this->_cache[$checksum][$key] = $result[$key];
            }
          }
        }
      }

      return $this->_cache[$checksum];
    }

    public function validate(&$fields) {

      if (empty($this->modules)) return false;

      foreach ($this->modules as $module) {
        if (!method_exists($module, 'validate')) continue;

        $result = $module->validate($fields);

        if (!empty($result['error'])) return $result;

        if (is_array($result)) {
          $fields = array_replace($fields, array_intersect_key($result, $fields));
        }
      }

      return true;
    }

    public function update($customer, $previous=[]) {
      return $this->run('update', null, $customer, $previous);
    }

    public function delete($customer) {
      return $this->run('delete', null, $customer);
    }

    public function run($method_name, $module_id=null) {

      if (!empty($module_id)) {
       $modules = [$module_id];
      } else {
        $modules = array_keys($this->modules);
      }

      foreach ($modules as $module_id) {
        if (method_exists($this->modules[$module_id], $method_name)) {
          return call_user_func_array([$this->modules[$module_id], $method_name], array_slice(func_get_args(), 2));
        }
      }
    }
  }
