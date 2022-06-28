<?php

  class ref_country {

    private $_country_code;
    private $_language_codes;
    private $_data = [];

    function __construct($country_code) {

      if (!preg_match('#[A-Z]{2}#', $country_code)) {
        trigger_error('Invalid country code ('. $country_code .')', E_USER_WARNING);
      }

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

    private function _load($field) {

      switch($field) {

        case 'zones':

          $this->_data['zones'] = [];

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."zones
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

          $row = database::query(
            "select * from ". DB_TABLE_PREFIX ."countries
            where iso_code_2 = '". database::input($this->_country_code) ."'
            limit 1;"
          )->fetch();

          if (!$row) {
            $country = new ent_country();
            $this->_data = $country->data;
            return;
          }

          foreach ($row as $key => $value) {
            $this->_data[$key] = $value;
          }

          break;
      }
    }

    public function format_address($address) {

      $address = [
        '%code' => fallback($address['code']),
        '%tax_id' => fallback($address['tax_id']),
        '%company' => fallback($address['company']),
        '%firstname' => fallback($address['firstname']),
        '%lastname' => fallback($address['lastname']),
        '%address1' => fallback($address['address1']),
        '%address2' => fallback($address['address2']),
        '%city' => fallback($address['city']),
        '%postcode' => fallback($address['postcode']),
        '%country_number' => $this->iso_code_1,
        '%country_code' => $this->iso_code_2,
        '%country_code_3' => $this->iso_code_3,
        '%country_name' => $this->name,
        '%country_comestic_name' => $this->domestic_name,
        '%zone_code' => fallback($address['zone_code']),
        '%zone_name' => (!empty($address['zone_code']) && !empty($this->zones[$address['zone_code']])) ? $this->zones[$address['zone_code']]['name'] : '',
      ];

      $output = strtr($this->address_format, $address);
      $output = preg_replace('#(\r\n?|\n)+#', "\r\n", $output);

      return trim($output);
    }

    public function in_geo_zone($geo_zones, $address=[]) {

      $args = func_get_args();

      if (is_numeric($args[1]) || (is_array($args[1]) && count($args[1]) === count(array_filter($args[1], 'is_array')) && count($args[1]) === count(array_filter($args[1], 'is_numeric')))) {
        trigger_error('Passing geo zone last preceeded by zone is deprecated. Instead do \$country->in_geo_zones($geo_zones, $address)', E_USER_DEPRECATED);
        list($zone_code, $geo_zones) = $args;
        $address = [
          'country_code' => $this->_country_code,
          'zone_code' => $zone_code,
          'city' => '',
        ];
      }

      if (!is_array($geo_zones)) $geo_zones = [$geo_zones];

      $zones_to_geo_zones_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
        where geo_zone_id in ('". implode("', '", database::input($geo_zones)) ."')
        ". (!empty($address['country_code']) ? "and (country_code = '' or country_code = '". database::input($address['country_code']) ."')" : "and (country_code = '' or country_code = '". database::input($this->_country_code) ."')") ."
        ". (!empty($address['zone_code']) ? "and (zone_code = '' or zone_code = '". database::input($address['zone_code']) ."')" : "and zone_code = ''") ."
        ". (!empty($address['city']) ? "and (city = '' or city like '". database::input($address['city']) ."')" : "and city = ''") ."
        limit 1;"
      );

      if (database::num_rows($zones_to_geo_zones_query)) return true;
    }
  }
