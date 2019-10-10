<?php

  class tax {

    private static $_cache = array();

    ######################################################################

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

      $tax_rates = self::get_rates($tax_class_id, $customer);

      $tax = 0;
      foreach ($tax_rates as $tax_rate) {
        switch($tax_rate['type']) {
          case 'fixed':
            $tax += $tax_rate['rate'];
            break;
          case 'percent':
            $val = $value / 100 * $tax_rate['rate'];
            $tax += $value / 100 * $tax_rate['rate'];
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
        switch(strtolower($customer)) {

          case 'store':
            $customer = array(
              'tax_id' => false,
              'company' => false,
              'country_code' => settings::get('store_country_code'),
              'zone_code' => settings::get('store_zone_code'),
              'shipping_address' => array(
                'company' => false,
                'country_code' => settings::get('store_country_code'),
                'zone_code' => settings::get('store_zone_code'),
              ),
            );
            break;

          case 'customer':
            $customer = array(
              'tax_id' => !empty(customer::$data['tax_id']) ? true : false,
              'company' => !empty(customer::$data['company']) ? true : false,
              'country_code' => customer::$data['country_code'],
              'zone_code' => customer::$data['zone_code'],
              'shipping_address' => array(
                'company' => customer::$data['shipping_address']['company'],
                'country_code' => customer::$data['shipping_address']['country_code'],
                'zone_code' => customer::$data['shipping_address']['zone_code'],
              ),
            );

            if (empty(customer::$data['different_shipping_address'])) {
              $customer['shipping_address'] = array(
                'company' => customer::$data['company'],
                'country_code' => customer::$data['country_code'],
                'zone_code' => customer::$data['zone_code'],
              );
            }
            break;

          default:
            trigger_error('Unknown preset for customer', E_USER_WARNING);
            break;
        }
      }

      if ($customer['country_code'] === null) {
        trigger_error('No country_code for tax passed', E_USER_WARNING);
        $customer['country_code'] = (!empty(customer::$data['country_code'])) ? customer::$data['country_code'] : settings::get('default_country_code');
        if ($customer['zone_code'] === null) {
          $customer['zone_code'] = (!empty(customer::$data['zone_code'])) ? customer::$data['zone_code'] : settings::get('default_zone_code');
        }
      }

      $checksum = md5(http_build_query($customer));

      if (isset(self::$_cache['rates'][$tax_class_id][$checksum])) return self::$_cache['rates'][$tax_class_id][$checksum];

      $tax_rates_query = database::query(
        "select * from ". DB_TABLE_TAX_RATES ."
        where tax_class_id = ". (int)$tax_class_id ."
        and (
          (
            address_type = 'payment'
            and geo_zone_id in (
              select geo_zone_id from ". DB_TABLE_ZONES_TO_GEO_ZONES ."
              where country_code = '". database::input($customer['country_code']) ."'
              and (zone_code = '' or zone_code = '". database::input($customer['zone_code']) ."')
            )
          ) or (
            address_type = 'shipping'
            and geo_zone_id in (
              select geo_zone_id from ". DB_TABLE_ZONES_TO_GEO_ZONES ."
              where country_code = '". database::input($customer['shipping_address']['country_code']) ."'
              and (zone_code = '' or zone_code = '". database::input($customer['shipping_address']['zone_code']) ."')
            )
          )
        )
        ". ((!empty($customer['company']) && !empty($customer['tax_id'])) ? "and rule_companies_with_tax_id" : "") ."
        ". ((!empty($customer['company']) && empty($customer['tax_id'])) ? "and rule_companies_without_tax_id" : "") ."
        ". ((empty($customer['company']) && !empty($customer['tax_id'])) ? "and rule_individuals_with_tax_id" : "") ."
        ". ((empty($customer['company']) && empty($customer['tax_id'])) ? "and rule_individuals_without_tax_id" : "") ."
        ;"
      );

      while ($rate = database::fetch($tax_rates_query)) {
        $tax_rates[$rate['id']] = $rate;
      }

      self::$_cache['rates'][$tax_class_id][$checksum] = $tax_rates;

      return $tax_rates;
    }

    public static function get_class_name($tax_class_id) {

      $tax_class_query = database::query(
        "select name from ". DB_TABLE_TAX_CLASSES ."
        where id = " . (int)$tax_class_id . "
        limit 1;"
      );

      if ($tax_class = database::fetch($tax_class_query)) {
        return $tax_class['name'];
      }

      return false;
    }

    public static function get_rate_name($tax_rate_id) {

      $tax_rates_query = database::query(
        "select name from ". DB_TABLE_TAX_RATES ."
        where id = " . (int)$tax_rate_id . "
        limit 1;"
      );

      if ($tax_rate = database::fetch($tax_rates_query)) {
        return $tax_rate['name'];
      }

      return false;
    }
  }
