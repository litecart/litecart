<?php

  class mod_order_total extends abs_modules {
    private $_shopping_cart;

    public function __construct($shopping_cart) {
      $this->_shopping_cart = $shopping_cart;
      $this->load();
    }

    public function process() {

      $output = [];

      if (empty($this->modules)) return $output;

      foreach ($this->modules as $module_id => $module) {
        if ($rows = $module->process($this->_shopping_cart)) {
          foreach ($rows as $row) {

            if (!empty($row['tax_class_id'])) {
              $row['tax'] = tax::get_tax($row['amount'], $row['tax_class_id'], $this->_shopping_cart->data['customer']);
            }

          // Round currency amount (Gets rid of hidden decimals)
            $row['amount'] = currency::round($row['amount'], $this->_shopping_cart->data['currency_code']);
            $row['tax'] = currency::round($row['tax'], $this->_shopping_cart->data['currency_code']);

            if (empty($row['amount']) && isset($row['value'])){
              $row['amount'] = $row['value']; // Backwards compatibility LiteCart <3.0
            }

            $output[] = [
              'module_id' => $module_id,
              'title' => $row['title'],
              'amount' => $row['amount'],
              'tax' => $row['tax'],
              'calculate' => !empty($row['calculate']) ? 1 : 0,
            ];
          }
        }
      }

      return $output;
    }
  }
