<?php

  class mod_order_total extends module {
    public $options;
    public $rows = array();

    public function __construct() {

      parent::set_type('order_total');

      $this->load();
    }

    public function process($order) {

      if (empty($this->modules)) return;

      foreach ($this->modules as $module_id => $module) {
        if ($rows = $module->process($order)) {
          foreach ($rows as $row) {

          // Round amounts
            if (settings::get('round_amounts')) {
              $row['value'] = currency::round($row['value'], $order->data['currency_code']);
              $row['tax'] = currency::round($row['tax'], $order->data['currency_code']);
            }

            $order->add_ot_row(array(
              'id' => $module_id,
              'title' => $row['title'],
              'value' => $row['value'],
              'tax' => $row['tax'],
              'calculate' => !empty($row['calculate']) ? 1 : 0,
            ));
          }
        }
      }
    }

    public function run($method_name, $module_id) {
      if (method_exists($this->modules[$module_id], $method_name)) {
        return call_user_func_array(array($this->modules[$module_id], $method_name), array_slice(func_get_args(), 2));
      }
    }
  }

?>