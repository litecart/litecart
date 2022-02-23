<?php

  class tax {

    private static $_cache = [];

    ######################################################################

    public static function get_price(float $value, int $tax_class_id, bool $calculate_tax=null, $customer=null) {
      if ($calculate_tax === null) $calculate_tax = !empty(customer::$data['display_prices_including_tax']) ? true : false;

      if ($calculate_tax) {
        return $value + self::get_tax($value, $tax_class_id, $customer);
      } else {
        return $value;
      }
    }

    public static function get_tax(float $value, int $tax_class_id, $customer=null) {

      if (!$value || !$tax_class_id) return 0;

      $tax_rates = self::get_rates($tax_class_id, $customer);

      $tax = 0;
      foreach ($tax_rates as $tax_rate) {
        $tax += $value * $tax_rate['rate'] / 100;
      }

      return $tax;
    }

    public static function get_rates(int $tax_class_id, $customer=null) {

      if (empty($tax_class_id)) return [];

      if (empty($customer)) $customer = 'customer';

    // Presets
      if (is_string($customer)) {
        switch(strtolower($customer)) {

          case 'site':
          case 'store':

            $customer = [
              'tax_id' => false,
              'company' => false,
              'country_code' => settings::get('site_country_code'),
              'zone_code' => settings::get('site_zone_code'),
              'city' => '',
              'shipping_address' => [
                'company' => false,
                'country_code' => settings::get('site_country_code'),
                'zone_code' => settings::get('site_zone_code'),
                'city' => '',
              ],
            ];

            break;

          case 'customer':

            $customer = [
              'tax_id' => !empty(customer::$data['tax_id']) ? true : false,
              'company' => !empty(customer::$data['company']) ? true : false,
              'country_code' => customer::$data['country_code'],
              'zone_code' => customer::$data['zone_code'],
              'city' => customer::$data['city'],
              'shipping_address' => [
                'company' => customer::$data['shipping_address']['company'],
                'country_code' => customer::$data['shipping_address']['country_code'],
                'zone_code' => customer::$data['shipping_address']['zone_code'],
                'city' => customer::$data['shipping_address']['city'],
              ],
            ];

            if (empty(customer::$data['different_shipping_address'])) {
              $customer['shipping_address'] = [
                'company' => customer::$data['company'],
                'country_code' => customer::$data['country_code'],
                'zone_code' => customer::$data['zone_code'],
                'city' => customer::$data['city'],
              ];
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

      $checksum = crc32(http_build_query($customer));

      if (isset(self::$_cache['rates'][$tax_class_id][$checksum])) {
        return self::$_cache['rates'][$tax_class_id][$checksum];
      }

      $tax_rates_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."tax_rates
        where tax_class_id = ". (int)$tax_class_id ."
        and (
          (
            address_type = 'payment'
            and geo_zone_id in (
              select geo_zone_id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
              where country_code = '". database::input($customer['country_code']) ."'
              and (zone_code = '' or zone_code = '". database::input($customer['zone_code']) ."')
              and (city = '' or city like '". database::input($customer['city']) ."')
            )
          ) or (
            address_type = 'shipping'
            and geo_zone_id in (
              select geo_zone_id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
              where country_code = '". database::input($customer['shipping_address']['country_code']) ."'
              and (zone_code = '' or zone_code = '". database::input($customer['shipping_address']['zone_code']) ."')
              and (city = '' or city like '". database::input($customer['shipping_address']['city']) ."')
            )
          )
        )
        ". ((!empty($customer['company']) && !empty($customer['tax_id'])) ? "and rule_companies_with_tax_id" : "") ."
        ". ((!empty($customer['company']) && empty($customer['tax_id'])) ? "and rule_companies_without_tax_id" : "") ."
        ". ((empty($customer['company']) && !empty($customer['tax_id'])) ? "and rule_individuals_with_tax_id" : "") ."
        ". ((empty($customer['company']) && empty($customer['tax_id'])) ? "and rule_individuals_without_tax_id" : "") ."
        ;"
      );

      $tax_rates = [];
      while ($rate = database::fetch($tax_rates_query)) {
        $tax_rates[$rate['id']] = $rate;
      }

      self::$_cache['rates'][$tax_class_id][$checksum] = $tax_rates;

      return $tax_rates;
    }
  }
