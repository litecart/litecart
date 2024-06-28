<?php

  $customer = database::query(
    "select * from ". DB_TABLE_PREFIX ."customers
    where id = '". database::input($_REQUEST['customer_id']) ."'
    limit 1;"
  )->fetch();

  if (!$customer) exit;

  $json = [
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
    'email' => fallback($customer['email']),
    'default_billing_address_id' => fallback($customer['default_billing_address_id']),
    'default_shipping_address_id' => fallback($customer['default_shipping_address_id']),
  ];

  ob_clean();
  header('Content-type: text/plain; charset='. mb_http_output());
  echo json_encode($json, JSON_UNESCAPED_SLASHES);
  exit;
