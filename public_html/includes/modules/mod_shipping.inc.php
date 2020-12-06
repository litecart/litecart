<?php

  class mod_shipping extends abs_module {
    private $_cache = [];
    public $selected = [];

    public function __construct($selected=[]) {

      if (!empty($selected)) {
        $this->selected = $selected;
      }

    // Load modules
      $this->load();

    // Attach userdata to module
      if (!empty($this->selected)) {
        list($module_id, $option_id) = explode(':', $this->selected['id']);
        if (!empty($this->modules[$module_id])) $this->modules[$module_id]->userdata = &$this->selected['userdata'][$module_id];
      }
    }

    public function select($module_id, $option_id, $userdata=[]) {

      if (empty($this->_cache['options'])) return;

      if (empty($option_id) && strpos($module_id, ':') !== false) {
        list($module_id, $option_id) = explode(':', $module_id);
      }

      $this->selected = [];

      $last_checksum = @end(array_keys($this->_cache['options']));
      $options = $this->_cache['options'][$last_checksum];
      $key = $module_id.':'.$option_id;

      if (!isset($options[$key])) return;
      if (!empty($options[$key]['error'])) return;

      $this->selected = [
        'module_id' => $module_id,
        'option_id' => $option_id,
        'icon' => $options[$key]['icon'],
        'title' => $options[$key]['title'],
        'cost' => $options[$key]['cost'],
        'tax_class_id' => $options[$key]['tax_class_id'],
        'userdata' => $userdata,
      ];
    }

    public function options($items, $currency_code=null, $customer=null) {

      if (empty($items) || empty($this->modules)) return [];

      if ($currency_code === null) $currency_code = currency::$selected['code'];
      if ($customer === null) $customer = customer::$data;

      $subtotal = ['amount' => 0, 'tax' => 0];
      foreach ($items as $item) {
        $subtotal['amount'] += $item['price'] * $item['quantity'];
        $subtotal['tax'] += $item['tax'] * $item['quantity'];
      }

      $checksum = crc32(http_build_query($items).http_build_query($customer));

      if (isset($this->_cache['options'][$checksum])) {
        return $this->_cache['options'][$checksum];
      }

      $this->_cache['options'][$checksum] = [];

      foreach ($this->modules as $module) {

        if (!$options = $module->options($items, $subtotal['amount'], $subtotal['tax'], $currency_code, $customer)) continue;
        if (!empty($options['options'])) $options = $options['options']; // Backwards compatibility

        foreach ($options as $option) {

          if (empty($option['title']) && isset($option['name'])) $option['title'] = $option['name']; // Backwards compatibility

          $this->_cache['options'][$checksum][$module->id.':'.$option['id']] = [
            'module_id' => $module->id,
            'option_id' => $option['id'],
            'icon' => $option['icon'],
            'title' => $option['title'],
            'description' => !empty($option['fields']) ? $option['description'] : '',
            'fields' => !empty($option['fields']) ? $option['fields'] : '',
            'cost' => (float)$option['cost'],
            'tax_class_id' => (int)$option['tax_class_id'],
            'exclude_cheapest' => !empty($option['exclude_cheapest']) ? true : false,
            'error' => !empty($option['error']) ? $option['error'] : false,
          ];
        }
      }

      return $this->_cache['options'][$checksum];
    }

    public function cheapest($items, $currency_code=null, $customer=null) {

      $checksum = crc32(http_build_query($items).http_build_query($customer));

      if (isset($this->_cache['options'][$checksum])) {
        $options = $this->_cache['options'][$checksum];
      } else {
        $options = $this->options($items, $currency_code, $customer);
      }

      if (empty($options)) return false;

      foreach ($options as $option) {
        if (!empty($option['error'])) continue;
        if (!empty($option['exclude_cheapest'])) continue;
        if (empty($cheapest) || $option['cost'] < $cheapest['cost']) {
          $cheapest = [
            'module_id' => $option['module_id'],
            'option_id' => $option['option_id'],
            'cost' => $option['cost'],
            'tax_class_id' => $option['tax_class_id'],
          ];
        }
      }

      if (empty($cheapest)) {
        foreach ($options as $module) {
          foreach ($module['options'] as $option) {
            if (!empty($option['error'])) continue;
            if (empty($cheapest) || $option['cost'] < $cheapest['cost']) {
              $cheapest = [
                'module_id' => $module['id'],
                'option_id' => $option['id'],
                'cost' => $option['cost'],
                'tax_class_id' => $option['tax_class_id'],
              ];
            }
          }
        }
      }

      if (empty($cheapest)) return false;

      return $cheapest;
    }

    public function after_process($order) {
      return $this->run('after_process', null, $order);
    }

    public function run($method_name, $module_id=null) {

      if (empty($module_id)) {
        if (empty($this->selected['id'])) return;
        list($module_id, $option_id) = explode(':', $this->selected['id']);
      }

      if (method_exists($this->modules[$module_id], $method_name)) {
        return call_user_func_array([$this->modules[$module_id], $method_name], array_slice(func_get_args(), 2));
      }
    }
  }
