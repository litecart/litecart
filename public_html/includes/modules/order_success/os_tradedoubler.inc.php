<?php

  class os_tradedoubler {
    public $id = __CLASS__;
    public $name = 'Tradedoubler Sales Tracking';
    public $description = '';
    public $author = 'ACME Corporation';
    public $version = '1.0';
    public $support_link = 'http://www.tradedoubler.com';
    public $website = 'http://www.tradedoubler.com';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
    }
    
    public function process() {
      global $order;
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if (empty($_SESSION["TRADEDOUBLER"]) && empty($_COOKIE["TRADEDOUBLER"])) return;
      
      $params = array(
        'organization' => $this->settings['organization_id'],
        'event' => $this->settings['event_id'],
        'orderNumber' => $order->data['id'],
        'checksum' => md5($this->settings['checksum_code'] . $order->data['id'] . $order->data['payment_due']),
        'tduid' => !empty($_COOKIE["TRADEDOUBLER"]) ? $_COOKIE["TRADEDOUBLER"] : $_SESSION["TRADEDOUBLER"],
        'reportInfo' => '',
        'orderValue' => $order->data['payment_due'],
        'currency' => $this->system->settings->get('store_currency_code'),
      );
      
      $output = '<img src="'. $this->system->document->link('https://tbs.tradedoubler.com/report', $params) .'" width="0" height="0" border="0" />';
      
      return $output;
    }
    
    function settings() {
       
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Status'),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'organization_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_organization_id', 'Organization ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_orgnization_id', 'Your Organization ID provided by Tradedoubler.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'event_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_event_id', 'Event ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_event_id', 'Your Event ID provided by Tradedoubler.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'event_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_checksum_code', 'Checksum Code'),
          'description' => $this->system->language->translate(__CLASS__.':description_checksum_code', 'Your Checksum Code provided by Tradedoubler, used for calculating the checksum.'),
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