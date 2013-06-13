<?php
  
  class lib_settings {
    private $cache;
    private $system;
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
      
      $configuration_query = $this->system->database->query(
        "select * from ". DB_TABLE_SETTINGS ."
        where `type` = 'global';"
      );
      while ($row = $this->system->database->fetch($configuration_query)) {
        $this->cache[$row['key']] = $row['value'];
      }
      
    // Set time zone
      date_default_timezone_set($this->get('store_timezone'));
    }
    
    //public function initiate() {
    //}
    
    //public function startup() {
    //}
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    public function before_output() {
    }
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function get($key, $default=null) {
      
      if (!isset($this->cache[$key])) {
        $configuration_query = $this->system->database->query(
          "select * from ". DB_TABLE_SETTINGS ."
          where `key` = '". $this->system->database->input($key) ."'
          limit 1;"
        );
        
        if (!$this->system->database->num_rows($configuration_query)) {
          trigger_error('Unsupported settings key ('. $key .')', E_USER_WARNING);
        }
        
        while ($row = $this->system->database->fetch($configuration_query)) {
          $this->cache[$key] = $row['value'];
        }
      }
      
      if (!isset($this->cache[$key])) {
        if ($default === null) {
          
          return;
        } else {
          return $default;
        }
      }
      
      return $this->cache[$key];
    }
    
    public function set($key, $value) {
      $this->cache[$key] = $value;
    }
  }
  
?>