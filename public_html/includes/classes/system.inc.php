<?php

// Set up compatibility for old $system object;
  class old_system {
    private $_module;
    
    public function __construct($module) {
      $this->_module = $module;
    }
    
    public function &__get($variable) {
      $class = ($this->_module);
      return $class::$$variable;
    }
    
    public function __set($variable, $value) {
      $class = ($this->_module);
      $class::$$variable = &$value;
    }
    
    public function __call($method, $arguments) {
      return forward_static_call_array(array($this->_module, $method), $arguments);
    }
  }
  
// Set up the system object 
  class system {
    private static $_loaded_modules = array();
    private static $_modules;
    
  // Compatibility with $system->module;
    public function __get($module) {
    
      if (empty(self::$_modules[$module])) {
        self::$_modules[$module] = new old_system($module);
      }
      
      //trigger_error("\$system->$module is deprecated, use $module::\$var or $module::method() instead", E_USER_DEPRECATED);
      
      return self::$_modules[$module];
    }
    
  // Autoload library modules
    public static function init() {
      
      foreach(glob(FS_DIR_HTTP_ROOT . WS_DIR_LIBRARY . 'lib_*.inc.php') as $file) {
        self::load($file);
      }
    }
    
  // Load library objects
    public static function load($file) {
      
      $module = preg_replace('/^lib_(.*)\.inc\.php$/', '$1', basename($file));
      
      if (in_array($module, self::$_loaded_modules)) {
        trigger_error("Module '$module' is already loaded", E_USER_WARNING);
        return;
      }
      
      require_once vqmod::modcheck($file);
      
      if (method_exists($module, 'construct')) {
        forward_static_call(array($module, 'construct'));
      }
      
      self::$_loaded_modules[] = $module;
    }
    
    public static function run($method_name) {
      foreach (self::$_loaded_modules as $library_module) {
        if (method_exists($library_module, $method_name)) {
          forward_static_call(array($library_module, $method_name));
        }
      }
    }
    
    public static function get_loaded_modules() {
      return self::$_loaded_modules;
    }
  }
  
?>