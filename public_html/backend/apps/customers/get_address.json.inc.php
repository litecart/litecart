<?php

  $customer_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."customers
    where id = '". database::input($_REQUEST['customer_id']) ."'
    limit 1;"
  );

  if (!$customer = database::fetch($customer_query)) exit;

  $json = [
    'email' => !empty($customer['email']) ? $customer['email'] : '',
    'tax_id' => !empty($customer['tax_id']) ? $customer['tax_id'] : '',
    'company' => !empty($customer['company']) ? $customer['company'] : '',
    'firstname' => !empty($customer['firstname']) ? $customer['firstname'] : '',
    'lastname' => !empty($customer['lastname']) ? $customer['lastname'] : '',
    'address1' => !empty($customer['address1']) ? $customer['address1'] : '',
    'address2' => !empty($customer['address2']) ? $customer['address2'] : '',
    'postcode' => !empty($customer['postcode']) ? $customer['postcode'] : '',
    'city' => !empty($customer['city']) ? $customer['city'] : '',
    'country_code' => !empty($customer['country_code']) ? $customer['country_code'] : '',
    'zone_code' => !empty($customer['zone_code']) ? $customer['zone_code'] : '',
    'phone' => !empty($customer['phone']) ? $customer['phone'] : '',
    'different_shipping_address' => !empty($customer['different_shipping_address']) ? true : false,
    'shipping_address' => [
      'company' => !empty($customer['shipping_company']) ? $customer['shipping_company'] : '',
      'firstname' => !empty($customer['shipping_firstname']) ? $customer['shipping_firstname'] : '',
      'lastname' => !empty($customer['shipping_lastname']) ? $customer['shipping_lastname'] : '',
      'address1' => !empty($customer['shipping_address1']) ? $customer['shipping_address1'] : '',
      'address2' => !empty($customer['shipping_address2']) ? $customer['shipping_address2'] : '',
      'postcode' => !empty($customer['shipping_postcode']) ? $customer['shipping_postcode'] : '',
      'city' => !empty($customer['shipping_city']) ? $customer['shipping_city'] : '',
      'country_code' => !empty($customer['shipping_country_code']) ? $customer['shipping_country_code'] : '',
      'zone_code' => !empty($customer['shipping_zone_code']) ? $customer['shipping_zone_code'] : '',
      'phone' => !empty($customer['shipping_phone']) ? $customer['shipping_phone'] : '',
    ],
  ];

  ob_clean();
  header('Content-type: text/plain; charset='. mb_http_output());
  echo json_encode($json, JSON_UNESCAPED_SLASHES);
  exit;
