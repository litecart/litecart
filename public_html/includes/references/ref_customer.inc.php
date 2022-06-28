<?php

  class ref_customer {

    private $_data = [];

    function __construct($customer_id) {
      $this->_data['id'] = (int)$customer_id;
    }

    public function &__get($name) {

      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }

      $this->_data[$name] = null;
      $this->_load($name);

      return $this->_data[$name];
    }

    public function &__isset($name) {
      return $this->__get($name);
    }

    public function __set($name, $value) {
      trigger_error('Setting data is prohibited', E_USER_WARNING);
    }

    private function _load($field) {

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
