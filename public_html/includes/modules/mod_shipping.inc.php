<?php

  class mod_shipping extends abs_modules {
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

    public function select($id, $userdata=[]) {

      if (empty($this->_cache['options'])) return;

      $this->selected = null;

      $options = array_slice($this->_cache['options'], -1)[0];

      if (($key = array_search($id, array_combine(array_keys($options), array_column($options, 'id')))) === false) {
        return;
      }  if (!empty($this->data['options'][$key]['error'])) {
        return;
      }

      list($module_id, $option_id) = explode(':', $id);
      if (method_exists($this->modules[$module_id], 'select')) {
        if ($error = $this->modules[$module_id]->select($option_id)) {
          notices::add('errors', $error);
        }
      }

      $this->selected = [
        'id' => $module_id.':'.$option_id,
        'module_id' => $module_id,
        'option_id' => $option_id,
        'icon' => $options[$key]['icon'],
        'name' => $options[$key]['name'],
        'fee' => $options[$key]['fee'],
        'tax_class_id' => $options[$key]['tax_class_id'],
        'incoterm' => $options[$key]['incoterm'],
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
        if (!empty($options['options'])) $options = $options['options']; // Backwards compatibility LiteCart <3.0.0

        foreach ($options as $option) {

          if (empty($option['title']) && isset($option['name'])) $option['title'] = $option['name']; // Backwards compatibility LiteCart <3.0.0
          if (empty($option['fee']) && isset($option['cost'])) $option['fee'] = $option['cost']; // Backwards compatibility LiteCart <3.0.0

          $this->_cache['options'][$checksum][] = [
            'id' => $module->id.':'.$option['id'],
            'module_id' => $module->id,
            'option_id' => $option['id'],
            'icon' => $option['icon'],
            'name' => $option['name'],
            'description' => !empty($option['fields']) ? $option['description'] : '',
            'fields' => !empty($option['fields']) ? $option['fields'] : '',
            'fee' => (float)$option['fee'],
            'tax_class_id' => (int)$option['tax_class_id'],
            'incoterm' => !empty($option['incoterm']) ? $option['incoterm'] : settings::get('default_incoterm'),
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
        if (empty($cheapest) || $option['fee'] < $cheapest['fee']) {
          return $option;
        }
      }
    }

    public function after_process($order) {
      return $this->run('after_process', null, $order);
    }

    public function track($order) {
      return $this->run('track', null, $order);
    }
  }
