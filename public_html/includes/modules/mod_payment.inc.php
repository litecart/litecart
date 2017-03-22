<?php

  class mod_payment extends module {
    public $data;

    public function __construct() {

    // Link data to session object
      if (!isset(session::$data['payment']) || !is_array(session::$data['payment'])) {
        session::$data['payment'] = array();
      }
      $this->data = &session::$data['payment'];

      if (empty($this->data['selected'])) {
        $this->data['selected'] = array();
      }

      if (!isset($this->data['userdata'])) {
        $this->data['userdata'] = array();
      }

    // Load modules
      $this->load('payment');

    // Attach userdata to module
      if (!empty($this->data['selected'])) {
        list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
        if (!empty($this->modules[$module_id])) $this->modules[$module_id]->userdata = &$this->data['userdata'][$module_id];
      }
    }

    public function options($items=null, $subtotal=null, $tax=null, $currency_code=null, $customer=null) {

      if ($items === null) $items = cart::$items;
      if ($subtotal === null) $subtotal = cart::$total['value'];
      if ($tax === null) $tax = cart::$total['tax'];
      if ($currency_code === null) $currency_code = currency::$selected['code'];
      if ($customer === null) $customer = customer::$data;

      $this->data['options'] = array();

      if (empty($this->modules)) return;

      foreach ($this->modules as $module) {

        $module_options = $module->options($items, $subtotal, $tax, $currency_code, $customer);

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

    public function select($module_id, $option_id, $userdata=null) {

      if (!isset($this->data['options'][$module_id]['options'][$option_id])) {
        $this->data['selected'] = array();
        notices::add('errors', language::translate('error_invalid_payment_option', 'Cannot set an invalid payment option.'));
        return;
      }

      if (!empty($this->data['options'][$module_id]['options'][$option_id]['error'])) {
        notices::add('errors', language::translate('error_cannot_select_payment_option_with_error', 'Cannot set a payment option that contains errors.'));
        return;
      }

      if (!empty($userdata)) {
        $this->data['userdata'][$module_id] = $userdata;
      }

      if (method_exists($this->modules[$module_id], 'select')) {
        if ($error = $this->modules[$module_id]->select($option_id)) {
          notices::add('errors', $error);
        }
      }

      $this->data['selected'] = array(
        'id' => $module_id.':'.$option_id,
        'icon' => $this->data['options'][$module_id]['options'][$option_id]['icon'],
        'title' => $this->data['options'][$module_id]['title'],
        'name' => $this->data['options'][$module_id]['options'][$option_id]['name'],
        'cost' => $this->data['options'][$module_id]['options'][$option_id]['cost'],
        'tax_class_id' => $this->data['options'][$module_id]['options'][$option_id]['tax_class_id'],
        'confirm' => $this->data['options'][$module_id]['options'][$option_id]['confirm'],
      );
    }

    public function cheapest($items=null, $subtotal=null, $tax=null, $currency_code=null, $customer=null) {

      $this->options($items, $subtotal, $tax, $currency_code, $customer);

      foreach ($this->data['options'] as $module) {
        foreach ($module['options'] as $option) {
          if (!empty($option['error'])) continue;
          if (!empty($option['exclude_cheapest'])) continue;
          if (empty($cheapest) || $option['cost'] < $cheapest['cost']) {
            $cheapest = array(
              'cost' => $option['cost'],
              'module_id' => $module['id'],
              'option_id' => $option['id'],
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
                'cost' => $option['cost'],
                'module_id' => $module['id'],
                'option_id' => $option['id'],
              );
            }
          }
        }
      }

      if (empty($cheapest)) return false;

      return $cheapest['module_id'].':'.$cheapest['option_id'];
    }

    public function pre_check($order) {

      if (empty($this->data['selected']['id'])) return;

      list($module_id, $option_id) = explode(':', $this->data['selected']['id']);

      if (!method_exists($this->modules[$module_id], 'pre_check')) return;

      return $this->modules[$module_id]->pre_check($order);
    }

    public function transfer($order) {

      if (empty($this->data['selected']['id'])) return;

      list($module_id, $option_id) = explode(':', $this->data['selected']['id']);

      if (!method_exists($this->modules[$module_id], 'transfer')) return;

      return $this->modules[$module_id]->transfer($order);
    }

    public function verify($order) {

      if (empty($this->data['selected']['id'])) return;

      list($module_id, $option_id) = explode(':', $this->data['selected']['id']);

      if (!method_exists($this->modules[$module_id], 'verify')) return;

      return $this->modules[$module_id]->verify($order);
    }

    public function after_process($order) {

      if (empty($this->data['selected']['id'])) return;

      list($module_id, $option_id) = explode(':', $this->data['selected']['id']);

      if (!method_exists($this->modules[$module_id], 'after_process')) return;

      return $this->modules[$module_id]->after_process($order);
    }

    public function receipt($order) {

      if (empty($this->data['selected']['id'])) return;

      list($module_id, $option_id) = explode(':', $this->data['selected']['id']);

      if (!method_exists($this->modules[$module_id], 'receipt')) return;

      return $this->modules[$module_id]->receipt($order);
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
