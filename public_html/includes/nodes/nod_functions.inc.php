<?php

  class functions {

    public static function __callstatic($function, $arguments) {

      $search_replace = [
        '#^form_draw_users_list#' => 'form_administrators_list',
        '#^form_draw_weight_classes_list#' => 'form_weight_units',
        '#^form_draw_length_classes_list#' => 'form_length_units',
        '#^form_draw_order_status_list#' => 'form_order_statuses',
        '#^form_draw_(form_)?(.*)#' => 'form_$2',
      ];

      foreach ($search_replace as $search => $replace) {
        if (preg_match($search, $function)) {
          $new_function = preg_replace($search, $replace, $function);
          trigger_error('Function '. $function.'() has been renamed to '. $new_function .'()', E_USER_DEPRECATED);
          $function = $new_function;
          break;
        }
      }

      if (!function_exists($function)) {
        $file = 'func_' . strtok($function, '_') .'.inc.php';
        include_once 'app://includes/functions/' . $file;
      }

      return call_user_func_array($function, $arguments);
    }
  }
