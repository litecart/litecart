<?php

// Set up the system object 
  class system {
    
    private $_loaded_modules;
    
    public function __construct() {
    // Autoload library modules
      foreach(glob(FS_DIR_HTTP_ROOT . WS_DIR_LIBRARY . '*.inc.php') as $module) {
        $this->load(str_replace('.inc.php', '', basename($module)));
      }
    }
    
  // Load library objects
    public function load($class_name) {
      if (isset($this->_loaded_modules[$class_name])) die("Module '$class_name' is already loaded");
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_LIBRARY . $class_name . '.inc.php');
      $this->$class_name = new $class_name($this);
      $this->_loaded_modules[$class_name] = $class_name;
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