<?php

  class job_currency_updater {
    public $id = __CLASS__;
    public $name = 'Currency Updater';
    public $description = '';
    public $author = 'ACME Corporation';
    public $version = '1.0';
    public $support_link = 'http://www.google.com/finance/converter';
    public $website = 'http://www.tim-international.net';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
    }
    
    public function process() {
      
      if ($this->settings['status'] != 'Enabled') return;
      
      switch ($this->settings['update_frequency']) {
        case 'Daily':
          if (strtotime($this->system->settings->get('currencies_last_updated')) > strtotime('-1 hour')) return; 
          break;
        case 'Weekly':
          if (strtotime($this->system->settings->get('currencies_last_updated')) > strtotime('-1 week')) return; 
          break;
        case 'Monthly':
          if (strtotime($this->system->settings->get('currencies_last_updated')) > strtotime('-1 month')) return; 
          break;
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". date('Y-m-d H:i:s') . "'
        where `key` = 'currencies_last_updated'
        limit 1;"
      );
      
      foreach (array_keys($this->system->currency->currencies) as $currency_code) {
        
        if ($currency_code == $this->system->settings->get('store_currency_code')) continue;
        
        $rawdata = $this->system->functions->http_request('http://www.google.com/ig/calculator?hl=en&q=1'. $this->system->settings->get('store_currency_code') .'=?'. $currency_code);
        
        if (empty($rawdata)) trigger_error('Could not update currency value for '. $currency_code, E_USER_WARNING);
        
        $data = explode('"', $rawdata);
        $data = explode(' ', $data['3']);
        $value = $data['0'];
        
        if (empty($value)) continue;

        $this->system->database->query(
          "update ". DB_TABLE_CURRENCIES ."
          set value = '". $this->system->database->input($value) ."'
          where code = '". $this->system->database->input($currency_code) ."'
          limit 1;"
        );
      }
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
          'key' => 'update_frequency',
          'default_value' => 'Daily',
          'title' => $this->system->language->translate(__CLASS__.':title_update_frequency', 'Update Frequency'),
          'description' => $this->system->language->translate(__CLASS__.':description_update_frequency', 'How often the currency values should be updated.'),
          'function' => 'radio("Daily","Weekly","Monthly")',
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
    
    public function install() {
      $this->system->database->query(
        "insert into ". DB_TABLE_SETTINGS ."
        (title, description, `key`, value, date_created, date_updated)
        values ('Currencies Last Updated', 'Time when currencies where last updated by the background job.', 'currencies_last_updated', '', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
      );
    }
    
    public function uninstall() {
      $this->system->database->query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = 'currencies_last_updated'
        limit 1;"
      );
    }
  }
  
?>