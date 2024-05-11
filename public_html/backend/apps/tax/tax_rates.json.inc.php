<?php

  try {

    $customer = [
      'billing_address' => [
        'tax_id' => fallback($_REQUEST['billing_address']['tax_id'], ''),
        'company' => fallback($_REQUEST['billing_address']['company'], ''),
        'country_code' => fallback($_REQUEST['billing_address']['country_code'], settings::get('site_country_code')),
        'zone_code' => fallback($_REQUEST['billing_address']['zone_code'], settings::get('site_zone_code')),
        'city' => fallback($_REQUEST['billing_address']['city'], ''),
      ],
      'shipping_address' => [
        'tax_id' => fallback($_REQUEST['shipping_address']['tax_id'], $_REQUEST['billing_address']['tax_id'], ''),
        'company' => fallback($_REQUEST['shipping_address']['company'], $_REQUEST['billing_address']['company'], ''),
        'country_code' => fallback($_REQUEST['shipping_address']['country_code'], $_REQUEST['billing_address']['country_code'], ''),
        'zone_code' => fallback($_REQUEST['shipping_address']['zone_code'], $_REQUEST['billing_address']['zone_code'], ''),
        'city' => fallback($_REQUEST['shipping_address']['city'], $_REQUEST['billing_address']['city'], ''),
      ],
    ];

    $tax_rates = database::query(
      "select * from ". DB_TABLE_PREFIX ."tax_classes
      order by code, name;"
    )->fetch_all(function($tax_class) use ($customer) {
      return tax::get_rates($tax_class['id'], $customer);
    });


  } catch (Exception $e) {
    http_response_code($e->getCode());
    notices::add('errors', $e->getMessage());
  }

	header('Content-Type: application/json');
	echo json_encode($tax_rates);
	exit;
