<?php

  class os_affiliator {
    public $id = __CLASS__;
    public $name = 'Affiliator Pixel';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.affliator.com';
    public $website = 'http://www.affliator.com';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
    }
    
    public function process() {
      global $order;
      
      if ($this->settings['status'] != 'Enabled') return;
      
    // Tweak per domain
      switch (substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], '.'))) {
        case '.dk':
          $this->settings['program_id'] = '731';
          $this->settings['currency_code'] = 'DKK';
          break;
        case '.no':
          $this->settings['program_id'] = '730';
          $this->settings['currency_code'] = 'NOK';
          break;
        case '.se':
          $this->settings['program_id'] = '729';
          $this->settings['currency_code'] = 'SEK';
          break;
      }
      
      if (empty($this->settings['program_id'])) return;
      if (empty($this->settings['currency_code'])) return;
      
      $subtotal = 0;
      foreach (array_keys($order->data['items']) as $key) {
        $subtotal += $order->data['items'][$key]['price'] * $order->data['items'][$key]['quantity'];
      }
      
      $params = array(
        'program' => $this->settings['program_id'],
        'order_id' => $order->data['id'],
        'order_amount' => $this->system->currency->convert($subtotal, null, $this->settings['currency_code']),
        'report_name' => 'sale',
      );
      
      $output = '<!-- BOF: '. $this->name .' -->' . PHP_EOL
              . '<img src="'. $this->system->document->link('https://report.affiliator.com/report_info.php', $params) .'" width="0" height="0" />' . PHP_EOL
              . '<!-- EOF: '. $this->name .' -->' . PHP_EOL;
      
      return $output;
    }
    
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
          'key' => 'program_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_program_id', 'Program ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_program_id', 'Your Program ID provided by Affiliator.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'currency_code',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_currency_code', 'Currency Code'),
          'description' => $this->system->language->translate(__CLASS__.':description_currency_code', 'Send the amount in the following currency.'),
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