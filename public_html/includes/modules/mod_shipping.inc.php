<?php

  class mod_shipping extends abs_module {
    public $data = array();
    public $items = array();

    public function __construct() {

      if (!isset(session::$data['shipping']) || !is_array(session::$data['shipping'])) {
        session::$data['shipping'] = array();
      }

      $this->data = &session::$data['shipping'];

      if (empty($this->data['selected'])) {
        $this->data['selected'] = array();
      }

      if (!isset($this->data['userdata'])) {
        $this->data['userdata'] = array();
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

      if (empty($items) || empty($this->modules)) return array();

      if ($currency_code === null) $currency_code = currency::$selected['code'];
      if ($customer === null) $customer = customer::$data;

      $subtotal = array('amount' => 0, 'tax' => 0);
      foreach ($items as $item) {
        $subtotal['amount'] += $item['price'] * $item['quantity'];
        $subtotal['tax'] += $item['tax'] * $item['quantity'];
      }

      $this->data['options'] = array();

      foreach ($this->modules as $module) {

        $module_options = $module->options($items, $subtotal['amount'], $subtotal['tax'], $currency_code, $customer);

        if (empty($module_options['options'])) continue;

        $this->data['options'][$module->id] = $module_options;
        $this->data['options'][$module->id]['id'] = $module->id;
        $this->data['options'][$module->id]['options'] = array();

        foreach ($module_options['options'] as $option) {

          $this->data['options'][$module->id]['options'][$option['id']] = array(
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
          );
        }
      }

      return $this->data['options'];
    }

    public function select($module_id, $option_id, $userdata=null) {

      $this->data['selected'] = array();

      if (!isset($this->data['options'][$module_id]['options'][$option_id])) {
        //notices::add('errors', language::translate('error_invalid_shipping_option', 'Cannot set an invalid shipping option.'));
        return;
      }

      if (!empty($this->data['options'][$module_id]['options'][$option_id]['error'])) {
        //notices::add('errors', language::translate('error_cannot_select_shipping_option_with_error', 'Cannot set a shipping option that contains errors.'));
        return;
      }

      if (!empty($userdata)) {
        $this->data['userdata'][$module_id] = $userdata;
      }

      $this->data['selected'] = array(
        'id' => $module_id.':'.$option_id,
        'icon' => $this->data['options'][$module_id]['options'][$option_id]['icon'],
        'title' => $this->data['options'][$module_id]['options'][$option_id]['title'],
        'name' => $this->data['options'][$module_id]['options'][$option_id]['name'],
        'cost' => $this->data['options'][$module_id]['options'][$option_id]['cost'],
        'tax_class_id' => $this->data['options'][$module_id]['options'][$option_id]['tax_class_id'],
      );
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
            $cheapest = array(
              'module_id' => $module['id'],
              'option_id' => $option['id'],
              'cost' => $option['cost'],
              'tax_class_id' => $option['tax_class_id'],
            );
          }
        }
      }

      if (empty($cheapest)) {
        foreach ($this->data['options'] as $module) {
          foreach ($module['options'] as $option) {
            if (!empty($option['error'])) continue;
            if (empty($cheapest) || $option['cost'] < $cheapest['cost']) {
              $cheapest = array(
                'module_id' => $module['id'],
                'option_id' => $option['id'],
                'cost' => $option['cost'],
                'tax_class_id' => $option['tax_class_id'],
              );
            }
          }
        }
      }

      if (empty($cheapest)) return false;

      return $cheapest;
    }

    public function after_process($order) {

      if (empty($this->data['selected']['id'])) return;

      list($module_id, $option_id) = explode(':', $this->data['selected']['id']);

      if (!method_exists($this->modules[$module_id], 'after_process')) return;

      return $this->modules[$module_id]->after_process($order);
    }

    public function run($method_name, $module_id=null) {

      if (empty($module_id)) {
        if (empty($this->data['selected']['id'])) return;
        list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
      }

      if (method_exists($this->modules[$module_id], $method_name)) {
        return call_user_func_array(array($this->modules[$module_id], $method_name), array_slice(func_get_args(), 2));
      }
    }
  }
