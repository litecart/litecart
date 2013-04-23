<?php

  class ot_payment_fee {
    private $system;
    public $id = __CLASS__;
    public $name = 'Payment Fee';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.forum.com';
    public $website = 'http://www.site.com';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
      
      $this->name = $this->system->language->translate(__CLASS__.':title_payment_fee', 'Payment Fee');
    }
    
    public function process() {
      global $payment;
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if (isset($payment->data['selected']['cost']) && $payment->data['selected']['cost'] == 0) return;
      
      $output = array();
      
      $output[] = array(
        'title' => $payment->data['selected']['title'] .' ('. $payment->data['selected']['name'] .')',
        'value' => $payment->data['selected']['cost'],
        'tax' => $this->system->tax->calculate($payment->data['selected']['cost'], $payment->data['selected']['tax_class_id'], true),
        'tax_class_id' => $payment->data['selected']['tax_class_id'],
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
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'priority',
          'default_value' => '30',
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