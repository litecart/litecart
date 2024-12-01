<?php

  try {

    $customer = [
      'tax_id' => !empty($_REQUEST['customer']['tax_id']) ? $_REQUEST['customer']['tax_id'] : '',
      'company' => !empty($_REQUEST['customer']['company']) ? $_REQUEST['customer']['company'] : '',
      'country_code' => !empty($_REQUEST['customer']['country_code']) ? $_REQUEST['customer']['country_code'] : settings::get('site_country_code'),
      'zone_code' => !empty($_REQUEST['customer']['zone_code']) ? $_REQUEST['customer']['zone_code'] : settings::get('site_zone_code'),
      'city' => !empty($_REQUEST['customer']['city']) ? $_REQUEST['customer']['city'] : '',
      'shipping_address' => [
        'company' => !empty($_REQUEST['customer']['shipping_address']['company']) ? $_REQUEST['customer']['shipping_address']['company'] : $_REQUEST['customer']['company'],
        'country_code' => !empty($_REQUEST['customer']['shipping_address']['country_code']) ? $_REQUEST['customer']['shipping_address']['country_code'] : $_REQUEST['customer']['country_code'],
        'zone_code' => !empty($_REQUEST['customer']['shipping_address']['zone_code']) ? $_REQUEST['customer']['shipping_address']['zone_code'] : $_REQUEST['customer']['shipping_Address']['zone_code'],
        'city' => !empty($_REQUEST['customer']['shipping_address']['city']) ? $_REQUEST['customer']['shipping_address']['city'] : $_REQUEST['customer']['city'],
      ],
    ];

    $tax_rates = [];

    $tax_classes_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."tax_classes
      order by code, name;"
    );

    while ($tax_class = database::fetch($tax_classes_query)) {
      $tax_rates = tax::get_rates($tax_class['id'], $customer);
    }

  } catch (Exception $e) {
    http_response_code($e->getCode());
    notices::add('errors', $e->getMessage());
  }

  header('Content-Type: application/json');
  echo json_encode($tax_rates);
  exit;
