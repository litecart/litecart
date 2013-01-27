<?php

  class job_modification_scanner {
    public $id = __CLASS__;
    public $name = 'Modification Scanner';
    public $description = '';
    public $author = 'ACME Corporation';
    public $version = '1.0';
    public $support_link = '';
    public $website = 'http://www.tim-international.net';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
    }
    
    public function process() {
      
      if ($this->settings['status'] != 'Enabled') return;
      if (empty($this->settings['email_receipient'])) return;
      
      switch ($this->settings['check_frequency']) {
        case 'Daily':
          if (strtotime($this->system->settings->get('modification_scanner_last_run')) > strtotime('-1 day')) return; 
          break;
        case 'Weekly':
          if (strtotime($this->system->settings->get('modification_scanner_last_run')) > strtotime('-1 week')) return; 
          break;
        case 'Monthly':
          if (strtotime($this->system->settings->get('modification_scanner_last_run')) > strtotime('-1 month')) return; 
          break;
      }
      
      $database = FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'modifications.db';
      $directory = FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME;
      
      $this->references = array();
      $this->session = array();
      
      ob_start();
      $num_references = $this->load($database);
      $num_scanned = $this->scan($directory, ($num_references == 0) ? true : false);
      $this->update($database);
      $log = ob_get_clean();
      
      if (!empty($log)) {
        $from = $this->system->settings->get('store_email');
        $to = $this->settings['email_receipient'];
        $result = $this->system->functions->email_send($from, $to, 'Modification Report for '. $this->system->settings->get('store_name'), $log);
      }
    }
    
    function load($database) {
      
      if (!is_file($database)) {
        $this->log("$database is empty");
        file_put_contents($database, '');
        return 0;
      }
    
      $rows = file($database);
      
      if (!is_array($rows)) return 0;
      
      $count = 0;
      foreach ($rows as $row) {
        list($file, $checksum) = explode("\t", $row);
        $this->references[$file] = trim($checksum);
        $count++;
      }
      
      return $count;
    }
    
    function scan($directory, $initial_scan=false) {
      
      $count = 0;
      
      $files = glob($directory . '/*');
      
      if (is_array($files)) {
        
        foreach ($files as $file) {
          
          if (is_dir($file)) {
            $count += $this->scan($file, $initial_scan);
            
          } else {
            $count++;
            $checksum = md5_file($file);
            $this->session[$file] = $checksum;
            
            if (isset($this->archive[$file])) {
              if ($this->references[$file] != $checksum) $this->log("* $file has been modified");
            } else if (!$initial_scan) {
              $this->log("* $file is a new file");
            }
          }
        }
      }
      
      return $count;
    }
    
    function update($database) {
      
      file_put_contents($database, '');
      
      foreach ($this->session as $file => $checksum) {
        file_put_contents($database, "$file\t$checksum\n", FILE_APPEND);
      }
    }
    
    function log($message) {
      echo "$message\n";
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
          'key' => 'check_frequency',
          'default_value' => 'Daily',
          'title' => $this->system->language->translate(__CLASS__.':title_check_frequency', 'Check Frequency'),
          'description' => $this->system->language->translate(__CLASS__.':description_check_frequency', 'How often the modification scanner should run.'),
          'function' => 'radio("Daily","Weekly","Monthly")',
        ),
        array(
          'key' => 'email_receipient',
          'default_value' => $this->system->settings->get('store_email'),
          'title' => $this->system->language->translate(__CLASS__.':title_email_receipient', 'E-mail Receipient'),
          'description' => $this->system->language->translate(__CLASS__.':description_email_receipient', 'Send modification reports to the given e-mail address.'),
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
    
    public function install() {
      $this->system->database->query(
        "insert into ". DB_TABLE_SETTINGS ."
        (title, description, `key`, value, date_created, date_updated)
        values ('Modification Scanner Last Run', 'Time when modification scan was last performed by the background job.', 'modification_scanner_last_run', '', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
      );
    }
    
    public function uninstall() {
      $this->system->database->query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = 'modification_scanner_last_run'
        limit 1;"
      );
    }
  }
  
?>