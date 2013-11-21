<?php
  
  class functions {
  
    public static function construct() {
    }
    
    public static function __callstatic($function, $arguments) {
      
      $class = 'func_' . substr($function, 0, strpos($function, '_'));
      
      return forward_static_call_array(array($class, $function), $arguments);
    }
  }
  
?>