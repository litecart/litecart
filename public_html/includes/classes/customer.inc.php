<?php
  
  class customer extends module {
    private $_cache;

    public function __construct($type='session') {
      
      parent::set_type('customer');
      
      $this->load();
      
    }
    
    public function get_address($fields) {
      
      if (empty($this->modules)) return false;
      
      $this->_cache = &session::$data['get_address_cache'];
      
      foreach ($this->modules as $module) {
        $checksum = sha1(serialize($fields));
        if (isset($this->_cache[$checksum])) {
          $fields = $this->_cache[$checksum];
          continue;
        }
        if (method_exists($module, 'get_address')) {
          if ($result = $module->get_address($fields)) {
            if (is_array($result) && empty($result['error'])) {
              foreach ($result as $key => $value) {
                if (!empty($result[$key])) $fields[$key] = $result[$key];
              }
            }
          }
        }
      }
      
      
      $this->_cache[$checksum] = $fields;
      
      return $fields;
    }
    
    public function after_save($object) {
      
      if (empty($this->modules)) return false;
      
      foreach ($this->modules as $module) {
        if (method_exists($module, 'after_save')) {
          $module->after_save($object);
        }
      }
    }
  }
  
?>