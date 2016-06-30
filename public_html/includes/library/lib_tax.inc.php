<?php

  class tax {
    private static $_cache = array();

    public static function construct() {
    }

    //public static function load_dependencies() {
    //}

    //public static function initiate() {
    //}

    //public static function startup() {
    //}

    //public static function before_capture() {
    //}

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function calculate($value, $tax_class_id, $calculate=null, $country_code=null, $zone_code=null) {
      trigger_error('The method calculate() is deprecated, use instead get_price()', E_USER_DEPRECATED);

      $customer = array(
        'country_code' => $country_code,
        'zone_code' => $zone_code,
      );

      return self::get_price($value, $tax_class_id, $calculate, $customer);
    }

    public static function get_price($value, $tax_class_id, $calculate_tax=null, $customer=null) {
      if ($calculate_tax === null) $calculate_tax = !empty(customer::$data['display_prices_including_tax']) ? true : false;

      if ($calculate_tax) {
        return $value + self::get_tax($value, $tax_class_id, $customer);
      } else {
        return $value;
      }
    }

    public static function get_tax($value, $tax_class_id, $customer=null) {

      if ($value == 0) return 0;

      if ($tax_class_id == 0) return 0;

      $tax = 0;

      $tax_rates = self::get_rates($tax_class_id, $customer);

      foreach ($tax_rates as $tax_rate) {
        switch($tax_rate['type']) {
          case 'fixed':
            $tax += $tax_rate['rate'];
            break;
          case 'percent':
            $tax += ($value / 100 * $tax_rate['rate']);
            break;
        }
      }

      return $tax;
    }

    public static function get_tax_by_rate($value, $tax_class_id, $customer=null) {

      if ($value == 0) return 0;

      $tax_rates = array();

      foreach (self::get_rates($tax_class_id, $customer) as $tax_rate) {
        if (!isset($tax_rates[$tax_rate['id']])) {
          $tax_rates[$tax_rate['id']] = array(
            'id' => $tax_rate['id'],
            'name' => $tax_rate['name'],
            'tax' => 0,
          );
        }
        switch($tax_rate['type']) {
          case 'fixed':
            $tax_rates[$tax_rate['id']]['tax'] += $tax_rate['rate'];
            break;
          case 'percent':
            $tax_rates[$tax_rate['id']]['tax'] += ($value / 100 * $tax_rate['rate']);
            break;
        }
      }

      return $tax_rates;
    }

    public static function get_rates($tax_class_id, $customer='customer') {

      if (empty($tax_class_id)) return array();

      $tax_rates = array();

      if ($customer === null) $customer = 'customer';

    // Presets
      if (is_string($customer)) {
        if (strtolower($customer) == 'store') {
          $customer = array(
            'tax_id' => null,
            'company' => null,
            'country_code' => settings::get('store_country_code'),
            'zone_code' => settings::get('store_zone_code'),
          );
        } else if (strtolower($customer) == 'customer') {
          $customer = array(
            'tax_id' => customer::$data['tax_id'],
            'company' => customer::$data['company'],
            'country_code' => customer::$data['country_code'],
            'zone_code' => customer::$data['zone_code'],
          );
        } else {
          trigger_error('Unknown preset for customer', E_USER_WARNING);
        }
      }

      if ($customer['country_code'] === null) {
        trigger_error('No country_code for tax passed', E_USER_WARNING);
        $customer['country_code'] = (!empty(customer::$data['country_code'])) ? customer::$data['country_code'] : settings::get('default_country_code');
        if ($customer['zone_code'] === null) {
          $customer['zone_code'] = (!empty(customer::$data['zone_code'])) ? customer::$data['zone_code'] : settings::get('default_zone_code');
        }
      }

      $checksum = md5(implode('', array(
        $customer['country_code'],
        $customer['zone_code'],
        !empty($customer['company']) ? '1' : '0',
        !empty($customer['tax_id']) ? '1' : '0',
      )));

      if (isset(self::$_cache['rates'][$tax_class_id][$checksum])) return self::$_cache['rates'][$tax_class_id][$checksum];

      $tax_rates_query = database::query(
        "select tr.* from ". DB_TABLE_TAX_RATES ." tr
        left join ". DB_TABLE_GEO_ZONES . " gz on (gz.id = tr.geo_zone_id)
        left join ". DB_TABLE_ZONES_TO_GEO_ZONES ." z2gz on (z2gz.geo_zone_id = tr.geo_zone_id)
        where tr.tax_class_id = '" . (int)$tax_class_id . "'
        and z2gz.country_code = '" . database::input($customer['country_code']) . "'
        and (z2gz.zone_code = '' or z2gz.zone_code = '". database::input($customer['zone_code']) ."');"
      );

      while ($row = database::fetch($tax_rates_query)) {
        if ($row['customer_type'] == 'individuals' && !empty($customer['company'])) continue;
        if ($row['customer_type'] == 'companies' && empty($customer['company'])) continue;
        if ($row['tax_id_rule'] == 'without' && !empty($customer['tax_id'])) continue;
        if ($row['tax_id_rule'] == 'with' && empty($customer['tax_id'])) continue;
        $tax_rates[$row['id']] = $row;
      }

      self::$_cache['rates'][$tax_class_id][$checksum] = $tax_rates;

      return $tax_rates;
    }

    public static function get_class_name($tax_class_id) {

      $tax_class_query = database::query(
        "select name from ". DB_TABLE_TAX_CLASSES ."
        where id = '" . (int)$tax_class_id . "'
        limit 1;"
      );
      $tax_class = database::fetch($tax_class_query);

      if (isset($tax_class['name'])) return $tax_class['name'];

      return false;
    }

    public static function get_rate_name($tax_rate_id) {

      $tax_rates_query = database::query(
        "select name from ". DB_TABLE_TAX_RATES ."
        where id = '" . (int)$tax_rate_id . "'
        limit 1;"
      );
      $tax_rate = database::fetch($tax_rates_query);

      if (isset($tax_rate['name'])) return $tax_rate['name'];

      return false;
    }
  }

?>