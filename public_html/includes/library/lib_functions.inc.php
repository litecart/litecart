<?php
  
  class functions {
  
    public static function construct() {
    }
    
    public static function __callstatic($function, $arguments) {
      
      $class = 'func_' . substr($function, 0, strpos($function, '_'));
      
      return call_user_func_array($function, $arguments);
    }
  }
  
?>