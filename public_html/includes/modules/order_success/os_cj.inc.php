<?php

  class os_cj {
    public $id = __CLASS__;
    public $name = 'CJ Pixel Tracer';
    public $description = '';
    public $author = 'ACME Corporation';
    public $version = '1.0';
    public $support_link = 'http://www.cj.com';
    public $website = 'http://www.cj.com';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
    }
    
    public function process() {
      global $order;
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if (empty($this->settings['enterprise_id'])) return;
      if (empty($this->settings['action_id'])) return;
      
      $params = array(
        'CID' => $this->settings['enterprise_id'],
        'type' => $this->settings['action_id'],
        'OID' => $order->data['id'],
        'METHOD' => 'IMG',
        'CURRENCY' => $order->data['currency_code'],
      );
      
      $n = 0;
      foreach (array_keys($order->data['items']) as $key) {
        $n++;
        $params = array_merge($params, array(
          'ITEM'.$n => $order->data['items'][$key]['name'],
          'AMT'.$n => round($order->data['items'][$key]['price'] * $order->data['currency_value'], 2),
          'QTY'.$n => $order->data['items'][$key]['quantity'],
        ));
      }
      
      $output = '<!-- BOF: '. $this->name .' -->' . PHP_EOL
              . '<img src="'. $this->system->document->link('https://www.emjcd.com/u', $params) .'" width="1" height="1" />' . PHP_EOL
              . '<!-- EOF: '. $this->name .' -->' . PHP_EOL;
      
      return $output;
    }
    
    function settings() {
       
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', ''),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'enterprise_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_enterprise_id', 'Enterprise ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_enterprise_id', 'Your Enterprise ID provided by Comission Junction.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'action_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_action_id', 'Action ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_action_id', 'Your Action ID provided by Comission Junction.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
  
?>