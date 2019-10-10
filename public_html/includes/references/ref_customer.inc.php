<?php

  class ref_customer {

    private $_id;
    private $_data = array();

    function __construct($customer_id) {
      $this->_id = (int)$customer_id;
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

          $query = database::query(
            "select * from ". DB_TABLE_CUSTOMERS ."
            where id = ". (int)$this->_id ."
            limit 1;"
          );

          if (!$row = database::fetch($query)) return;

          $map = array(
            'id',
            'email',
            'password_hash',
            'tax_id',
            'company',
            'firstname',
            'lastname',
            'address1',
            'address2',
            'postcode',
            'country_code',
            'zone_code',
            'city',
            'phone',
            'different_shipping_address',
            'newsletter',
          );

          foreach ($map as $key) {
            $this->_data[$key] = $row[$key];
          }

          $key_map = array(
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
          );

          foreach ($key_map as $skey => $tkey) {
            $this->_data['shipping_address'][$tkey] = $row[$skey];
          }

          break;
      }
    }
  }
