<?php
  
  class mod_shipping extends module {
    public $data;
    public $cheapest = '';
    public $items = array();
    public $destination = array();

    public function __construct($type='session') {
      
      parent::set_type('shipping');
      
      switch($type) {
        case 'session': // Used for checkout
          if (!isset(session::$data['shipping']) || !is_array(session::$data['shipping'])) session::$data['shipping'] = array();
          $this->data = &session::$data['shipping'];
          
          foreach (cart::$data['items'] as $key => $item) {
            $this->items[$key] = $item;
          }
          
          $this->destination = customer::$data;
          
          break;
        case 'local':
          $this->data = array();
          break;
        default:
          trigger_error('Unknown type', E_USER_ERROR);
      }
      
      $this->load();
    }
    
    public function options($items=null, $subtotal=null, $tax=null, $currency_code=null, $customer=null) {
       
      if ($items === null) $items = cart::$data['items'];
      if ($subtotal === null) $subtotal = cart::$data['total']['value'];
      if ($tax === null) $tax = cart::$data['total']['tax'];
      if ($currency_code === null) $currency_code = currency::$selected['code'];
      if ($customer === null) $customer = customer::$data;

      $this->data['options'] = array();
      
      if (empty($this->modules)) return;
      
      foreach ($this->modules as $module) {
      
        $module_options = $module->options($items, $subtotal, $tax, $currency_code, $customer);
        
        if (!empty($module_options['options'])) {
        
          $this->data['options'][$module->id] = $module_options;
          $this->data['options'][$module->id]['id'] = $module->id;
          $this->data['options'][$module->id]['options'] = array();
          
          foreach ($module_options['options'] as $option) {
            $this->data['options'][$module->id]['options'][$option['id']] = $option;
          }
        }
      }
      
      return $this->data['options'];
    }
    
    public function select($module_id, $option_id) {
      
      if (!isset($this->data['options'][$module_id]['options'][$option_id])) {
        $this->data['selected'] = array();
        notices::add('errors', language::translate('error_invalid_shipping_option', 'Cannot set an invalid shipping option.'));
        return;
      }
      
      $this->data['selected'] = array(
        'id' => $module_id.':'.$option_id,
        'icon' => $this->data['options'][$module_id]['options'][$option_id]['icon'],
        'title' => $this->data['options'][$module_id]['title'],
        'name' => $this->data['options'][$module_id]['options'][$option_id]['name'],
        'cost' => $this->data['options'][$module_id]['options'][$option_id]['cost'],
        'tax_class_id' => $this->data['options'][$module_id]['options'][$option_id]['tax_class_id'],
      );
    }
    
    public function cheapest() {
    
      if (empty($this->data['options'])) $this->options();
      
      foreach ($this->data['options'] as $module) {
        foreach ($module['options'] as $option) {
          if (!isset($cheapest_amount) || $option['cost'] < $cheapest_amount) {
            $cheapest_amount = $option['cost'];
            $module_id = $module['id'];
            $option_id = $option['id'];
          }
        }
      }
      
      if (empty($module_id) || empty($option_id)) return false;
      
      return $module_id.':'.$option_id;
    }
    
    public function run($method_name, $module_id='') {
    
      if (empty($module_id)) {
        if (empty($this->data['selected']['id'])) return;
        list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
      }
      
      if (method_exists($this->modules[$module_id], $method_name)) {
        return $this->modules[$module_id]->$method_name();
      }
    }
  }
  
?>