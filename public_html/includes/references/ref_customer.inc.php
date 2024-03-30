<?php

  class ref_customer extends abs_reference_entity {

    protected $_data = [];

    function __construct($customer_id) {
      $this->_data['id'] = (int)$customer_id;
    }

    protected function _load($field) {

      switch($field) {

        default:

          $customer = database::query(
            "select * from ". DB_TABLE_PREFIX ."customers
            where id = ". (int)$this->_data['id'] ."
            limit 1;"
          )->fetch();

          if (!$customer) return;

          foreach ($customer as $key => $value) {
            $this->_data[$key] = $customer[$key];
          }

          break;
      }
    }
  }
