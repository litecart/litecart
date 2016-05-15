<?php

  class ref_customer {

    private $_id;
    private $_cache_id;
    private $_data = array();

    function __construct($customer_id) {

      $this->_id = (int)$customer_id;
      //$this->_cache_id = cache::cache_id('customer_'.(int)$customer_id);

      //if ($cache = cache::get($this->_cache_id, 'file')) {
        //$this->_data = $cache;
      //}
    }

    public function &__get($name) {

      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }

      $this->_data[$name] = null;
      $this->load($name);

      return $this->_data[$name];
    }

    public function &__isset($name) {
      return $this->__get($name);
    }

    public function __set($name, $value) {
      trigger_error('Setting data is prohibited', E_USER_WARNING);
    }

    private function load($field='') {

      switch($field) {

        default:

          $query = database::query(
            "select * from ". DB_TABLE_CUSTOMERS ."
            where id = '". database::input($this->_id) ."'
            limit 1;"
          );
          $row = database::fetch($query);
          if (empty($row)) trigger_error('Could not find customer ('. $this->_id .') in database.', E_USER_WARNING);

          if (database::num_rows($query) == 0) return;

          $map = array(
            'id',
            'email',
            'password',
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
            'mobile',
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
          );
          foreach ($key_map as $skey => $tkey) {
            $this->_data['shipping_address'][$tkey] = $row[$skey];
          }

          break;
      }

      //cache::set($this->_cache_id, 'file', $this->_data);
    }
  }

?>