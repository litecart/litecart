<?php

  try {

    $tax_rates = [];

    $tax_classes = database::query(
      "select * from ". DB_TABLE_PREFIX ."tax_classes
      order by code, name;"
    )->fetch_all();

    foreach ($tax_classes as $tax_class) {

      $_REQUEST['customer'] = [
        'billing_address' => [
          'tax_id' => isset($_REQUEST['customer']['tax_id']) ? $_REQUEST['customer']['tax_id'] : '',
          'company' => isset($_REQUEST['customer']['company']) ? $_REQUEST['customer']['company'] : '',
          'country_code' => isset($_REQUEST['customer']['country_code']) ? $_REQUEST['customer']['country_code'] : settings::get('store_country_code'),
          'zone_code' => isset($_REQUEST['customer']['zone_code']) ? $_REQUEST['customer']['zone_code'] : settings::get('store_zone_code'),
          'city' => isset($_REQUEST['customer']['city']) ? $_REQUEST['customer']['city'] : '',
        ],
        'shipping_address' => [
          'company' => isset($_REQUEST['shipping_address']['company']) ? $_REQUEST['shipping_address']['company'] : '',
          'country_code' => isset($_REQUEST['shipping_address']['country_code']) ? $_REQUEST['shipping_address']['country_code'] : settings::get('store_country_code'),
          'zone_code' => isset($_REQUEST['shipping_address']['zone_code']) ? $_REQUEST['shipping_address']['zone_code'] : settings::get('store_zone_code'),
          'city' => isset($_REQUEST['shipping_address']['city']) ? $_REQUEST['shipping_address']['city'] : '',
        ],
      ];

      if (empty($_REQUEST['customer']['country_code'])) {
        $_REQUEST['customer']['country_code'] = settings::get('store_country_code');
      }

      if (empty($_REQUEST['customer']['zone_code'])) {
        $_REQUEST['customer']['zone_code'] = settings::get('store_zone_code');
      }

      if (!isset($_REQUEST['customer']['city'])) {
        $_REQUEST['customer']['city'] = '';
      }

      if (empty($_REQUEST['shipping_address']['country_code'])) {
        $_REQUEST['shipping_address']['country_code'] = settings::get('store_country_code');
      }

      if (empty($_REQUEST['shipping_address']['zone_code'])) {
        $_REQUEST['shipping_address']['zone_code'] = settings::get('store_zone_code');
      }

      if (!isset($_REQUEST['shipping_address']['city'])) {
        $_REQUEST['shipping_address']['city'] = '';
      }

      $tax_rates = database::query(
        "select tax_class_id, code, name, rate
        from ". DB_TABLE_PREFIX ."tax_rates
        where (
          address_type = 'payment'
          and geo_zone_id in (
            select geo_zone_id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
            where country_code = '". database::input($_REQUEST['customer']['country_code']) ."'
            and (zone_code = '' or zone_code = '". database::input($_REQUEST['customer']['zone_code']) ."')
            and (city = '' or city like '". database::input($_REQUEST['customer']['city']) ."')
          )
        ) or (
          address_type = 'shipping'
          and geo_zone_id in (
            select geo_zone_id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
            where country_code = '". database::input($_REQUEST['shipping_address']['country_code']) ."'
            and (zone_code = '' or zone_code = '". database::input($_REQUEST['shipping_address']['zone_code']) ."')
            and (city = '' or city like '". database::input($_REQUEST['shipping_address']['city']) ."')
          )
        )
        ". ((!empty($_REQUEST['customer']['company']) && !empty($_REQUEST['customer']['tax_id'])) ? "and rule_companies_with_tax_id" : "") ."
        ". ((!empty($_REQUEST['customer']['company']) && empty($_REQUEST['customer']['tax_id'])) ? "and rule_companies_without_tax_id" : "") ."
        ". ((empty($_REQUEST['customer']['company']) && !empty($_REQUEST['customer']['tax_id'])) ? "and rule_individuals_with_tax_id" : "") ."
        ". ((empty($_REQUEST['customer']['company']) && empty($_REQUEST['customer']['tax_id'])) ? "and rule_individuals_without_tax_id" : "") .";"
      )->fetch_all();
    }

  } catch (Exception $e) {
    http_response_code($e->getCode());
    notices::add('errors', $e->getMessage());
  }

  header('Content-Type: application/json');
  echo json_encode($tax_rates);
  exit;
