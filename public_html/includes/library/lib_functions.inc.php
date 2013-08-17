<?php
  
  class lib_functions {
  
    public function __construct() {
    }
    
    public function __call($function, $arguments) {
      
      if (!function_exists($function)) {
        $function_file = FS_DIR_HTTP_ROOT . WS_DIR_FUNCTIONS . substr($function, 0, strpos($function, '_')).'.inc.php';
        require_once($function_file);
      }
      
      return call_user_func_array($function, $arguments);
    }
  }
  
?>