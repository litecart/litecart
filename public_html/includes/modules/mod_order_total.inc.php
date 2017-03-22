<?php

  class mod_order_total extends module {

    public function __construct() {
      $this->load('order_total');
    }

    public function process($order) {

      $output = array();

      if (empty($this->modules)) return $output;

      foreach ($this->modules as $module_id => $module) {
        if ($rows = $module->process($order)) {
          foreach ($rows as $row) {

          // Round amounts
            if (settings::get('round_amounts')) {
              $row['value'] = currency::round($row['value'], $order->data['currency_code']);
              $row['tax'] = currency::round($row['tax'], $order->data['currency_code']);
            }

            $output[] = array(
              'id' => $module_id,
              'title' => $row['title'],
              'value' => $row['value'],
              'tax' => $row['tax'],
              'calculate' => !empty($row['calculate']) ? 1 : 0,
            );
          }
        }
      }

      return $output;
    }

    public function run($method_name, $module_id) {
      if (method_exists($this->modules[$module_id], $method_name)) {
        return call_user_func_array(array($this->modules[$module_id], $method_name), array_slice(func_get_args(), 2));
      }
    }
  }
