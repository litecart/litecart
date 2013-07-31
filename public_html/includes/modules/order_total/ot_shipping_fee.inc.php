<?php

  class ot_shipping_fee {
    private $system;
    public $id = __CLASS__;
    public $name = 'Shipping Fee';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
      
      $this->name = $this->system->language->translate(__CLASS__.':title_shipping_fee', 'Shipping Fee');
    }
    
    public function process() {
      global $shipping, $order;
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if (empty($shipping->data['selected']['cost'])) return;
      
      $output = array();
      
      $output[] = array(
        'title' => $shipping->data['selected']['title'] .' ('. $shipping->data['selected']['name'] .')',
        'value' => $shipping->data['selected']['cost'],
        'tax' => $this->system->tax->calculate($shipping->data['selected']['cost'], $shipping->data['selected']['tax_class_id'], true),
        'tax_class_id' => $shipping->data['selected']['tax_class_id'],
        'calculate' => true,
      );
      
      if (!empty($this->settings['free_shipping_amount'])) {
      
      // Calculate cart total
        $subtotal = 0;
        foreach ($order->data['items'] as $item) {
          $subtotal += $item['quantity'] * $item['price'];
        }
        
      // If below minimum amount
        if ($subtotal >= $this->settings['free_shipping_amount']) {
          $output[] = array(
            'title' => $this->system->language->translate('title_free_shipping', 'Free Shipping'),
            'value' => -$shipping->data['selected']['cost'],
            'tax' => -$this->system->tax->calculate($shipping->data['selected']['cost'], $shipping->data['selected']['tax_class_id'], true),
            'tax_class_id' => $shipping->data['selected']['tax_class_id'],
            'calculate' => true,
          );
        }
      }
      
      return $output;
    }
    
    public function before_process() {}
    
    public function after_process() {}
    
    function settings() {
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'priority',
          'default_value' => '20',
          'title' => $this->system->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
        array(
          'key' => 'free_shipping_amount',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_free_shipping_amount', 'Free Shipping Amount'),
          'description' => $this->system->language->translate(__CLASS__.':description_free_shipping_amount', 'Enable free shipping for orders that meet the given cart total amount or above (excluding tax). 0 = disabled'),
          'function' => 'decimal()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
  
?>