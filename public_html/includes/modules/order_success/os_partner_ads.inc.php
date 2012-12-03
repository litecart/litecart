<?php

  class os_partner_ads {
    public $id = __CLASS__;
    public $name = 'Partner-Ads Pixel';
    public $description = '';
    public $author = 'ACME Corporation';
    public $version = '1.0';
    public $support_link = 'http://www.partner-ads.dk';
    public $website = 'http://www.partner-ads.dk';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
    }
    
    public function process() {
      global $order;
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if ($this->system->language->selected['code'] != 'da') return;
      
      if (empty($this->settings['program_id'])) return;
      
      $subtotal = 0;
      foreach (array_keys($order->data['items']) as $key) {
        $subtotal += $order->data['items'][$key]['price'] * $order->data['items'][$key]['quantity'];
        //$subtotal += $order->data['items'][$key]['tax'] * $order->data['items'][$key]['quantity'];
      }
      
      $params = array(
        'programid' => $this->settings['program_id'],
        'type' => 'salg',
        'ordrenummer' => (int)$order->data['id'],
        'varenummer' => 'x',
        'antal' => 1,
        'omprsalg' => $this->system->currency->convert($subtotal, null, 'DKK'),
      );
      
      $output = '<!-- BOF: '. $this->name .' -->' . PHP_EOL
              . '<img src="'. $this->system->document->link('http://www.partner-ads.com/dk/leadtrack.php', $params) .'" width="1" height="1" border="0" />' . PHP_EOL
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
          'key' => 'program_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_program_id', 'Program ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_program_id', 'Your program ID provided by Partner-Ads.'),
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