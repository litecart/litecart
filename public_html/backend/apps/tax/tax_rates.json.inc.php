<?php

  try {

    $tax_rates = [];

    $tax_classes_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."tax_classes
      order by code, name;"
    );

    while ($tax_class = database::fetch($tax_classes_query)) {

      if (empty($_GET['customer'])) {
        $_GET['customer'] = [
          'tax_id' => '',
          'company' => '',
          'country_code' => settings::get('site_country_code'),
          'zone_code' => settings::get('site_zone_code'),
          'city' => '',
          'shipping_address' => [
            'company' => '',
            'country_code' => settings::get('site_country_code'),
            'zone_code' => settings::get('site_zone_code'),
            'city' => '',
          ],
        ];
      }

      if (empty($_GET['customer']['country_code'])) $_GET['customer']['country_code'] = settings::get('site_country_code');
      if (empty($_GET['customer']['zone_code'])) $_GET['customer']['zone_code'] = settings::get('site_zone_code');
      if (!isset($_GET['customer']['city'])) $_GET['customer']['city'] = '';

      if (empty($_GET['customer']['shipping_address']['country_code'])) $_GET['customer']['shipping_address']['country_code'] = settings::get('site_country_code');
      if (empty($_GET['customer']['shipping_address']['zone_code'])) $_GET['customer']['shipping_address']['zone_code'] = settings::get('site_zone_code');
      if (!isset($_GET['customer']['shipping_address']['city'])) $_GET['customer']['shipping_address']['city'] = '';

      $tax_rates_query = database::query(
        "select `type`, code, name, sum(rate) as rate from ". DB_TABLE_PREFIX ."tax_rates
        where tax_class_id = ". (int)$tax_class['id'] ."
        and (
          (
            address_type = 'payment'
            and geo_zone_id in (
              select geo_zone_id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
              where country_code = '". database::input($_GET['customer']['country_code']) ."'
              and (zone_code = '' or zone_code = '". database::input($_GET['customer']['zone_code']) ."')
              and (city = '' or city like '". database::input($_GET['customer']['city']) ."')
            )
          ) or (
            address_type = 'shipping'
            and geo_zone_id in (
              select geo_zone_id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
              where country_code = '". database::input($_GET['customer']['shipping_address']['country_code']) ."'
              and (zone_code = '' or zone_code = '". database::input($_GET['customer']['shipping_address']['zone_code']) ."')
              and (city = '' or city like '". database::input($_GET['customer']['shipping_address']['city']) ."')
            )
          )
        )
        ". ((!empty($_GET['customer']['company']) && !empty($_GET['customer']['tax_id'])) ? "and rule_companies_with_tax_id" : "") ."
        ". ((!empty($_GET['customer']['company']) && empty($_GET['customer']['tax_id'])) ? "and rule_companies_without_tax_id" : "") ."
        ". ((empty($_GET['customer']['company']) && !empty($_GET['customer']['tax_id'])) ? "and rule_individuals_with_tax_id" : "") ."
        ". ((empty($_GET['customer']['company']) && empty($_GET['customer']['tax_id'])) ? "and rule_individuals_without_tax_id" : "") ."
        group by `type`;"
      );

      while ($tax_rate = database::fetch($tax_rates_query)) {

        if (!isset($tax_rates[$tax_class['id']])) {
          $tax_rates[$tax_class['id']] = [
            'code' => $tax_rate['code'],
            'name' => $tax_rate['name'],
            'rate' => 0,
            'fixed' => 0,
          ];
        }

        if ($tax_rate['type'] == 'fixed') {
          $tax_rates[$tax_class['id']]['fixed'] = (float)$tax_rate['rate'];
        } else {
          $tax_rates[$tax_class['id']]['rate'] = (float)$tax_rate['rate'];
        }
      }
    }

  } catch (Exception $e) {
    http_response_code($e->getCode());
    notices::add('errors', $e->getMessage());
  }

	header('Content-Type: application/json');
	echo json_encode($tax_rates);
	exit;
