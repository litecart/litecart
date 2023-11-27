<?php

  class mod_shipping extends abs_modules {
    private $_cache = [];
    private $_shopping_cart;
    private $_options = [];
    public $selected = [];

    public function __construct($shopping_cart=[], $selected=[]) {

      $this->_shopping_cart = $shopping_cart;

      if (!empty($selected['id'])) {
        $this->selected = $selected;
        list($module_id, $option_id) = explode(':', $this->selected['id']);
        $this->selected['module_id'] = $module_id;
        $this->selected['option_id'] = $option_id;
      }

    // Load modules
      $this->load();

    // Rettach userdata to module
      if (!empty($this->selected['userdata']) && !empty($this->modules[$this->selected['module_id']])) {
        $this->modules[$this->selected['module_id']]->userdata = &$this->selected['userdata'];
      }
    }

    public function select($id, $userdata=[]) {

      $this->selected = [];

      if (!$options = $this->options()) return;

      if (($key = array_search($id, array_combine(array_keys($options), array_column($options, 'id')))) === false) return;
      if (!empty($this->data['options'][$key]['error'])) return;

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

    public function options() {

      if (empty($this->modules)) return [];

      if (empty($this->_shopping_cart->data['items'])) return [];

      $subtotal = ['amount' => 0, 'tax' => 0];
      foreach ($this->_shopping_cart->data['items'] as $item) {
        $subtotal['amount'] += $item['price'] * $item['quantity'];
        $subtotal['tax'] += $item['tax'] * $item['quantity'];
      }

      $checksum = crc32(json_encode($this->_shopping_cart->data['items']));

      if (!empty($this->_cache[$checksum]['options'])) {
        return $this->_cache[$checksum]['options'];
      }

      $this->_options = [];

      foreach ($this->modules as $module) {

        $data = &$this->_shopping_cart->data;
        if (!$options = $module->options($data['items'], $data['subtotal'], $data['subtotal_tax'], $data['currency_code'], $data['customer'])) continue;

        if (!empty($options['options'])) {
          $options = $options['options']; // Backwards compatibility LiteCart <3.0.0
        }

        foreach ($options as $option) {

          if (empty($option['title']) && isset($option['name'])) {
            $option['title'] = $option['name']; // Backwards compatibility LiteCart <3.0.0
          }

          if (empty($option['fee']) && isset($option['cost'])) {
            $option['fee'] = $option['cost']; // Backwards compatibility LiteCart <3.0.0
          }

          $this->_cache[$checksum]['options'][] = [
            'id' => $module->id.':'.$option['id'],
            'module_id' => $module->id,
            'option_id' => $option['id'],
            'icon' => $option['icon'],
            'name' => $option['name'],
            'description' => fallback($option['description'], ''),
            'fields' => fallback($option['fields']),
            'fee' => (float)$option['fee'],
            'tax_class_id' => (int)$option['tax_class_id'],
            'incoterm' => fallback($option['incoterm'], settings::get('default_incoterm')),
            'exclude_cheapest' => !empty($option['exclude_cheapest']),
            'error' => fallback($option['error'], false),
          ];
        }
      }

      return $this->_cache[$checksum]['options'];
    }

    public function cheapest() {

      if (empty($this->_options)) {
        $options = $this->options($this->_shopping_cart->data['items'], $this->_shopping_cart->data['currency_code'], $this->_shopping_cart->data['customer']);
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
  }
