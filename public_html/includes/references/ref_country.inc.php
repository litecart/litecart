<?php

  class ref_country {

    private $_country_code;
    private $_language_codes;
    private $_data = array();

    function __construct($country_code) {

      if (!preg_match('#[A-Z]{2}#', $country_code)) trigger_error('Invalid country code ('. $country_code .')', E_USER_ERROR);

      $this->_country_code = $country_code;
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
      trigger_error('Setting data is prohibited ('.$name.')', E_USER_WARNING);
    }

    private function _load($field='') {

      switch($field) {

        case 'zones':

          $this->_data['zones'] = array();

          $query = database::query(
            "select * from ". DB_TABLE_ZONES ."
            where country_code = '". database::input($this->_country_code) ."'
            order by name;"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if ($key == 'country_code') continue;
              $this->_data['zones'][$row['code']] = $row;
            }
          }

          break;

        default:

          $query = database::query(
            "select * from ". DB_TABLE_COUNTRIES ."
            where iso_code_2 = '". database::input($this->_country_code) ."'
            limit 1;"
          );

          $row = database::fetch($query);

          if (database::num_rows($query) == 0) return;

          foreach ($row as $key => $value) $this->_data[$key] = $value;

          break;
      }
    }

    public function format_address($address) {

      $address = array(
        '%company' => !empty($address['company']) ? $address['company'] : '',
        '%firstname' => !empty($address['firstname']) ? $address['firstname'] : '',
        '%lastname' => !empty($address['lastname']) ? $address['lastname'] : '',
        '%address1' => !empty($address['address1']) ? $address['address1'] : '',
        '%address2' => !empty($address['address2']) ? $address['address2'] : '',
        '%city' => !empty($address['city']) ? $address['city'] : '',
        '%postcode' => !empty($address['postcode']) ? $address['postcode'] : '',
        '%country_code' => $address['country_code'],
        '%country_name' => $this->name,
        '%zone_code' => !empty($address['zone_code']) ? $address['zone_code'] : '',
        '%zone_name' => (!empty($address['zone_code']) && !empty($this->zones[$address['zone_code']])) ? $this->zones[$address['zone_code']]['name'] : '',
      );

      $output = strtr($this->address_format ? $this->address_format : settings::get('default_address_format'), $address);

      while (preg_match('#(\R\R)#', $output)) $output = preg_replace('#(\R\R)#', "\r\n", $output);

      return trim($output);
    }
  }
