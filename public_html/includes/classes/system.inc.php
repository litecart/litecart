<?php

// Set up the system object 
  class system {
    
    private $_loaded_modules;
    
    public function __construct() {
    // Autoload library modules
      foreach(glob(FS_DIR_HTTP_ROOT . WS_DIR_LIBRARY . 'lib_*.inc.php') as $module) {
        $module = preg_replace('/^lib_(.*)\.inc\.php$/', '$1', basename($module));
        $this->load($module);
      }
    }
    
  // Load library objects
    public function load($module) {
      $class_name = 'lib_'.$module;
      if (isset($this->_loaded_modules[$class_name])) trigger_error("Module '$module' is already loaded", E_USER_WARNING);
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_LIBRARY . $class_name . '.inc.php');
      $this->$module = new $class_name($this);
      $this->_loaded_modules[$module] = $module;
    }
    
  // Method to run methods ;)
    public function run($method_name) {
      foreach ($this->_loaded_modules as $module) {
        if (method_exists($this->$module, $method_name)) $this->$module->$method_name();
      }
    }
    
    public function get_loaded_modules() {
      return $this->_loaded_modules;
    }
  }
  
?>