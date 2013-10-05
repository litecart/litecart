<?php

  class job_currency_updater {
    public $id = __CLASS__;
    public $name = 'Currency Updater';
    public $description = 'Currency valuation by Google Finance';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    public $priority = 0;
    
    public function __construct() {
    }
    
    public function process() {
      
      if (empty($this->settings['status'])) return;
      
      switch ($this->settings['update_frequency']) {
        case 'Daily':
          if (strtotime($GLOBALS['system']->settings->get('currencies_last_updated')) > strtotime('-1 day')) return; 
          break;
        case 'Weekly':
          if (strtotime($GLOBALS['system']->settings->get('currencies_last_updated')) > strtotime('-1 week')) return; 
          break;
        case 'Monthly':
          if (strtotime($GLOBALS['system']->settings->get('currencies_last_updated')) > strtotime('-1 month')) return; 
          break;
      }
      
      $GLOBALS['system']->database->query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". date('Y-m-d H:i:s') . "'
        where `key` = 'currencies_last_updated'
        limit 1;"
      );
      
      foreach (array_keys($GLOBALS['system']->currency->currencies) as $currency_code) {
        
        if ($currency_code == $GLOBALS['system']->settings->get('store_currency_code')) continue;
        
        $rawdata = $GLOBALS['system']->functions->http_fetch('http://www.google.com/ig/calculator?hl=en&q=1'. $GLOBALS['system']->settings->get('store_currency_code') .'=?'. $currency_code);
        
        if (empty($rawdata)) trigger_error('Could not update currency value for '. $currency_code, E_USER_WARNING);
        
        $data = explode('"', $rawdata);
        $data = explode(' ', $data['3']);
        $value = $data['0'];
        
        if (empty($value)) continue;

        $GLOBALS['system']->database->query(
          "update ". DB_TABLE_CURRENCIES ."
          set value = '". $GLOBALS['system']->database->input($value) ."'
          where code = '". $GLOBALS['system']->database->input($currency_code) ."'
          limit 1;"
        );
      }
    }
    
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
          'key' => 'update_frequency',
          'default_value' => 'Daily',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_update_frequency', 'Update Frequency'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_update_frequency', 'How often the currency values should be updated.'),
          'function' => 'radio("Daily","Weekly","Monthly")',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {
      $GLOBALS['system']->database->query(
        "insert into ". DB_TABLE_SETTINGS ."
        (title, description, `key`, value, date_created, date_updated)
        values ('Currencies Last Updated', 'Time when currencies where last updated by the background job.', 'currencies_last_updated', '', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
      );
    }
    
    public function uninstall() {
      $GLOBALS['system']->database->query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = 'currencies_last_updated'
        limit 1;"
      );
    }
  }
  
?>