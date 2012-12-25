<?php

  class os_adsettings {
    public $id = __CLASS__;
    public $name = 'Adsettings Pixel';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.adsettings.com';
    public $website = 'http://www.adsettings.com';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
    }
    
    public function process() {
      global $order;
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if ($this->system->language->selected['code'] != 'sv') return;
      
      if (empty($this->settings['advertiser_id'])) return;
      
      $subtotal = 0;
      foreach (array_keys($order->data['items']) as $key) {
        $subtotal += $order->data['items'][$key]['price'] * $order->data['items'][$key]['quantity'];
        //$subtotal += $order->data['items'][$key]['tax'] * $order->data['items'][$key]['quantity'];
      }
      
      $params = array(
        'advertiser_id' => $this->settings['advertiser_id'],
        'order_id' => (int)$order->data['id'],
        'ordervalue' => $this->system->currency->convert($subtotal, null, 'SEK'),
      );
      
      $output = '<!-- BOF: '. $this->name .' -->' . PHP_EOL
              . '<iframe src="'. $this->system->document->link('http://www.adsettings.com/scripts/check_sale.php', $params) .'" scrolling="no" frameborder="0" width="1" height="1"></iframe>' . PHP_EOL
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
          'key' => 'advertiser_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_advertiser_id', 'Advertiser ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_advertiser_id', 'Your advertiser ID provided by Adsettings.'),
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