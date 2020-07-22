<?php

  class mod_shipping extends abs_module {
    public $data = [];
    public $items = [];

    public function __construct($selected=null, $userdata=null) {

      if (!empty($selected)) {
        $this->data['selected'] = $selected;
      }

      if (empty($this->data['selected'])) {
        $this->data['selected'] = [];
      }

      if (!empty($userdata)) {
        $this->data['userdata'] = $userdata;
      }

      if (!isset($this->data['userdata'])) {
        $this->data['userdata'] = [];
      }

    // Load modules
      $this->load('shipping');

    // Attach userdata to module
      if (!empty($this->data['selected'])) {
        list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
        if (!empty($this->modules[$module_id])) $this->modules[$module_id]->userdata = &$this->data['userdata'][$module_id];
      }
    }

    public function options($items=null, $currency_code=null, $customer=null) {

      if (empty($items) || empty($this->modules)) return [];

      if ($currency_code === null) $currency_code = currency::$selected['code'];
      if ($customer === null) $customer = customer::$data;

      $subtotal = ['amount' => 0, 'tax' => 0];
      foreach ($items as $item) {
        $subtotal['amount'] += $item['price'] * $item['quantity'];
        $subtotal['tax'] += $item['tax'] * $item['quantity'];
      }

      $this->data['options'] = [];

      foreach ($this->modules as $module) {

        $module_options = $module->options($items, $subtotal['amount'], $subtotal['tax'], $currency_code, $customer);

        if (empty($module_options['options'])) continue;

        $this->data['options'][$module->id] = $module_options;
        $this->data['options'][$module->id]['id'] = $module->id;
        $this->data['options'][$module->id]['options'] = [];

        foreach ($module_options['options'] as $option) {

          $this->data['options'][$module->id]['options'][$option['id']] = [
            'id' => $option['id'],
            'icon' => $option['icon'],
            'title' => !empty($option['title']) ? $option['title'] : $this->data['options'][$module->id]['title'],
            'name' => $option['name'],
            'description' => $option['description'],
            'fields' => !empty($option['fields']) ? $option['fields'] : '',
            'cost' => (float)$option['cost'],
            'tax_class_id' => (int)$option['tax_class_id'],
            'exclude_cheapest' => !empty($option['exclude_cheapest']) ? true : false,
            'error' => !empty($option['error']) ? $option['error'] : false,
          ];
        }
      }

      return $this->data['options'];
    }

    public function select($module_id, $option_id, $userdata=null) {

      if (empty($option_id) && strpos($module_id, ':') !== false) {
        list($module_id, $option_id) = explode(':', $module_id);
      }

      $this->data['selected'] = [];

      if (!isset($this->data['options'][$module_id]['options'][$option_id])) return;

      if (!empty($this->data['options'][$module_id]['options'][$option_id]['error'])) return;

      if (!empty($userdata)) {
        $this->data['userdata'][$module_id] = $userdata;
      }

      $this->data['selected'] = [
        'id' => $module_id.':'.$option_id,
        'icon' => $this->data['options'][$module_id]['options'][$option_id]['icon'],
        'title' => $this->data['options'][$module_id]['options'][$option_id]['title'],
        'name' => $this->data['options'][$module_id]['options'][$option_id]['name'],
        'cost' => $this->data['options'][$module_id]['options'][$option_id]['cost'],
        'tax_class_id' => $this->data['options'][$module_id]['options'][$option_id]['tax_class_id'],
      ];
    }

    public function cheapest($items=null, $currency_code=null, $customer=null) {

      if (empty($this->data['options'])) {
        $this->options($items, $currency_code, $customer);
      }

      if (empty($this->data['options'])) return false;

      foreach ($this->data['options'] as $module) {
        foreach ($module['options'] as $option) {
          if (!empty($option['error'])) continue;
          if (!empty($option['exclude_cheapest'])) continue;
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

      if (empty($cheapest)) {
        foreach ($this->data['options'] as $module) {
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
        if (empty($this->data['selected']['id'])) return;
        list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
      }

      if (method_exists($this->modules[$module_id], $method_name)) {
        return call_user_func_array([$this->modules[$module_id], $method_name], array_slice(func_get_args(), 2));
      }
    }
  }
