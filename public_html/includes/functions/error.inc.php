<?php
  
  function error_trigger_traced($message, $error_level, $depth=0) {
    $trace = debug_backtrace();
    echo count($trace);
    trigger_error($message .' in '. $trace[$depth+1]['file'] .' on line '. $trace[$depth+1]['line'], $error_level);
  }
  
?>