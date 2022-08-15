<?php

  class functions {

    public static function __callstatic($function, $arguments) {

      if (!function_exists($function)) {
        $file = 'func_' . strtok($function, '_') .'.inc.php';
        include_once 'app://includes/functions/' . $file;
      }

      return call_user_func_array($function, $arguments);
    }
  }
