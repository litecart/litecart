<?php

  class ref_customer extends abs_reference_entity {

    private $_data = [];

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

          $remap_keys = [
            'shipping_company' => 'company',
            'shipping_firstname' => 'firstname',
            'shipping_lastname' => 'lastname',
            'shipping_address1' => 'address1',
            'shipping_address2' => 'address2',
            'shipping_postcode' => 'postcode',
            'shipping_city' => 'city',
            'shipping_country_code' => 'country_code',
            'shipping_zone_code' => 'zone_code',
            'shipping_phone' => 'phone',
          ];

          foreach ($customer as $key => $value) {
            if (!in_array($key, array_keys($remap_keys))) continue;
            $this->_data[$key] = $customer[$key];
          }

          foreach ($remap_keys as $skey => $tkey) {
            $this->_data['shipping_address'][$tkey] = $customer[$skey];
          }

          break;
      }
    }
  }
