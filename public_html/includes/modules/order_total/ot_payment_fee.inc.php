<?php

  class ot_payment_fee {
    public $id = __CLASS__;
    public $name = 'Payment Fee';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    public $priority = 0;
    
    public function __construct() {
      
      $this->name = $GLOBALS['system']->language->translate(__CLASS__.':title_payment_fee', 'Payment Fee');
    }
    
    public function process() {
      global $payment;
      
      if (empty($this->settings['status'])) return;
      
      if (empty($payment->data['selected']['cost'])) return;
      
      $output = array();
      
      $output[] = array(
        'title' => $payment->data['selected']['title'] .' ('. $payment->data['selected']['name'] .')',
        'value' => $payment->data['selected']['cost'],
        'tax' => $GLOBALS['system']->tax->calculate($payment->data['selected']['cost'], $payment->data['selected']['tax_class_id'], true),
        'calculate' => true,
      );
      
      return $output;
    }
    
    public function before_process() {}
    
    public function after_process() {}
    
    function settings() {
      return array(
        array(
          'key' => 'status',
          'default_value' => '1',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ),
        array(
          'key' => 'priority',
          'default_value' => '30',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
    
?>