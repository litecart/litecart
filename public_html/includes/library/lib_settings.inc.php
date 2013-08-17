<?php
  
  class lib_settings {
    private $cache;
    
    public function __construct() {
    }
    
    public function load_dependencies() {
      
      $configuration_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_SETTINGS ."
        where `type` = 'global';"
      );
      while ($row = $GLOBALS['system']->database->fetch($configuration_query)) {
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
      
      if (isset($this->cache[$key])) return $this->cache[$key];
      
      $configuration_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_SETTINGS ."
        where `key` = '". $GLOBALS['system']->database->input($key) ."'
        limit 1;"
      );
      
      if (!$GLOBALS['system']->database->num_rows($configuration_query)) {
        if ($default === null) trigger_error('Unsupported settings key ('. $key .')', E_USER_WARNING);
        return $default;
      }
      
      while ($row = $GLOBALS['system']->database->fetch($configuration_query)) {
        $this->cache[$key] = $row['value'];
      }
      
      return $this->cache[$key];
    }
    
    public function set($key, $value) {
      $this->cache[$key] = $value;
    }
  }
  
?>