<?php

  class ot_subtotal {
    private $system;
    public $id = __CLASS__;
    public $name = 'Subtotal';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.forum.com';
    public $website = 'http://www.site.com';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
      
      $this->name = $this->system->language->translate(__CLASS__.':title_subtotal', 'Subtotal');
    }
    
    public function process() {
      global $order;
      
      $output = array();
      $value = 0;
      $tax = 0;
      
      foreach ($order->data['items'] as $item) {
        $value += $item['price'] * $item['quantity'];
        $tax += $item['tax'] * $item['quantity'];
      }
      
      $output[] = array(
        'title' => $this->system->language->translate('title_subtotal', 'Subtotal'),
        'value' => $value,
        'tax' => $tax,
        'tax_class_id' => 0,
        'calculate' => false,
      );
      
      return $output;
    }
    
    public function before_process() {}
    
    public function after_process() {}
    
    function settings() {
      return array(
        array(
          'key' => 'priority',
          'default_value' => '1',
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