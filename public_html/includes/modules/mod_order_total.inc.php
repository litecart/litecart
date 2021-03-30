<?php

  class mod_order_total extends abs_module {

    public function __construct() {
      $this->load();
    }

    public function process($order) {

      $output = [];

      if (empty($this->modules)) return $output;

      foreach ($this->modules as $module_id => $module) {
        if ($rows = $module->process($order, $output)) {
          foreach ($rows as $row) {

            if (!empty($row['tax_class_id'])) {
              $row['tax'] = tax::get_tax($row['value'], $row['tax_class_id'], $order->data['customer']);
            }

          // Round amounts
            if (settings::get('round_amounts')) {
              $row['value'] = currency::round($row['value'], $order->data['currency_code']);
              $row['tax'] = currency::round($row['tax'], $order->data['currency_code']);
            }

            $output[] = [
              'module_id' => $module_id,
              'title' => $row['title'],
              'value' => $row['value'],
              'tax' => $row['tax'],
              'calculate' => !empty($row['calculate']) ? 1 : 0,
            ];
          }
        }
      }

      return $output;
    }

    public function run($method_name, $module_id) {
      if (!empty($this->modules[$module_id]) && method_exists($this->modules[$module_id], $method_name)) {
        return call_user_func_array([$this->modules[$module_id], $method_name], array_slice(func_get_args(), 2));
      }
    }
  }
