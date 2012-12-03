<?php

  class job_error_reporter {
    public $id = __CLASS__;
    public $name = 'Error Reporter';
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
      if (empty($this->settings['email_receipient'])) return;
      
      switch ($this->settings['report_frequency']) {
        case 'Immediately':
          break;
        case 'Hourly':
          if (strtotime($this->system->settings->get('errors_last_reported')) > strtotime('-1 hour')) return; 
          break;
        case 'Daily':
          if (strtotime($this->system->settings->get('errors_last_reported')) > strtotime('-1 day')) return; 
          break;
        case 'Weekly':
          if (strtotime($this->system->settings->get('errors_last_reported')) > strtotime('-1 week')) return; 
          break;
        case 'Monthly':
          if (strtotime($this->system->settings->get('errors_last_reported')) > strtotime('-1 month')) return; 
          break;
      }
      
      $file = ini_get('error_log');
      $contents = file_get_contents($file);
      if (!empty($contents)) {
        $from = $this->system->settings->get('store_email');
        $to = $this->settings['email_receipient'];
        $result = $this->system->functions->email_send($from, $to, 'Error Report for '. $this->system->settings->get('store_name'), $contents);
        if ($result === true) {
          file_put_contents($file, '');
          $this->system->database->query(
            "update ". DB_TABLE_SETTINGS ."
            set value = '". date('Y-m-d H:i:s') . "'
            where `key` = 'errors_last_reported'
            limit 1;"
          );
        }
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
          'key' => 'email_receipient',
          'default_value' => $this->system->settings->get('store_email'),
          'title' => $this->system->language->translate(__CLASS__.':title_email_receipient', 'E-mail Receipient'),
          'description' => $this->system->language->translate(__CLASS__.':description_email_receipient', 'The e-mail address where reports will be sent.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'report_frequency',
          'default_value' => 'Daily',
          'title' => $this->system->language->translate(__CLASS__.':title_report_frequency', 'Report Frequency'),
          'description' => $this->system->language->translate(__CLASS__.':description_report_frequency', 'How often the reports should be sent.'),
          'function' => 'radio("Immediately","Hourly","Daily","Weekly","Monthly")',
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
        values ('Errors Last Reported', 'Time when errors where last reported by the background job.', 'errors_last_reported', '', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
      );
    }
    
    public function uninstall() {
      $this->system->database->query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = 'errors_last_reported'
        limit 1;"
      );
    }
  }
  
?>