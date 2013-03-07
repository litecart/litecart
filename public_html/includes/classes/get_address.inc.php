<?php
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'module.inc.php');
  
  class get_address extends module {
    private $_cache;

    public function __construct($type='session') {
      
      global $system;
      $this->system = $system;
      
      parent::set_type('get_address');
      
      $this->load();
      
      //$this->_cache = &$this->system->session->data['get_address_cache'];
    }
    
    public function query($data) {
      
      if (empty($this->modules)) return false;
      
      $checksum = sha1(serialize($data));
      
      //if (isset($this->_cache[$checksum])) return $this->_cache[$checksum];
      
      $this->_cache[$checksum] = array('error' => $this->system->language->translate('error_failed_getting_address'));
      
      foreach ($this->modules as $module) {
        if ($result = $module->query($data)) {
          if (empty($result['error'])) {
            $this->_cache[$checksum] = $result;
            return $result;
          }
        }
      }
    }
  }
  
?>