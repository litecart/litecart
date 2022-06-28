<?php

  $customer = database::query(
    "select * from ". DB_TABLE_PREFIX ."customers
    where id = '". database::input($_REQUEST['customer_id']) ."'
    limit 1;"
  )->fetch();

  if (!$customer) exit;

  $json = [
    'email' => fallback($customer['email']),
    'tax_id' => fallback($customer['tax_id']),
    'company' => fallback($customer['company']),
    'firstname' => fallback($customer['firstname']),
    'lastname' => fallback($customer['lastname']),
    'address1' => fallback($customer['address1']),
    'address2' => fallback($customer['address2']),
    'postcode' => fallback($customer['postcode']),
    'city' => fallback($customer['city']),
    'country_code' => fallback($customer['country_code']),
    'zone_code' => fallback($customer['zone_code']),
    'phone' => fallback($customer['phone']),
    'different_shipping_address' => !empty($customer['different_shipping_address']) ? true : false,
    'shipping_address' => [
      'company' => fallback($customer['shipping_company']),
      'firstname' => fallback($customer['shipping_firstname']),
      'lastname' => fallback($customer['shipping_lastname']),
      'address1' => fallback($customer['shipping_address1']),
      'address2' => fallback($customer['shipping_address2']),
      'postcode' => fallback($customer['shipping_postcode']),
      'city' => fallback($customer['shipping_city']),
      'country_code' => fallback($customer['shipping_country_code']),
      'zone_code' => fallback($customer['shipping_zone_code']),
      'phone' => fallback($customer['shipping_phone']),
    ],
  ];

  ob_clean();
  header('Content-type: text/plain; charset='. mb_http_output());
  echo json_encode($json, JSON_UNESCAPED_SLASHES);
  exit;
