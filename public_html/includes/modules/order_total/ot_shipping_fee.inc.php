<?php

  class ot_shipping_fee {
    private $system;
    public $id = __CLASS__;
    public $name = 'Shipping Fee';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.forum.com';
    public $website = 'http://www.site.com';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
      
      $this->name = $this->system->language->translate(__CLASS__.':title_shipping_fee', 'Shipping Fee');
    }
    
    public function process() {
      global $shipping;
      
      if (!isset($shipping->data['selected']['cost'])) return;
      
      $output = array();
      
      $output[] = array(
        'title' => $shipping->data['selected']['title'] .' ('. $shipping->data['selected']['name'] .')',
        'value' => $shipping->data['selected']['cost'],
        'tax' => $this->system->tax->calculate($shipping->data['selected']['cost'], $shipping->data['selected']['tax_class_id'], true),
        'tax_class_id' => $shipping->data['selected']['tax_class_id'],
        'calculate' => true,
      );
      
      return $output;
    }
    
    public function before_process() {}
    
    public function after_process() {}
    
    function settings() {
      return array(
        array(
          'key' => 'priority',
          'default_value' => '20',
          'title' => $this->system->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
  
?>