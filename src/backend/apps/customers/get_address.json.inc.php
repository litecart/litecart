<?php

	$customer = database::query(
		"select * from ". DB_TABLE_PREFIX ."customers
		where id = '". database::input($_REQUEST['customer_id']) ."'
		limit 1;"
	)->fetch();

	if (!$customer) exit;

	$json = [
		'tax_id' => $customer['tax_id'] ?? '',
		'company' => $customer['company'] ?? '',
		'firstname' => $customer['firstname'] ?? '',
		'lastname' => $customer['lastname'] ?? '',
		'address1' => $customer['address1'] ?? '',
		'address2' => $customer['address2'] ?? '',
		'postcode' => $customer['postcode'] ?? '',
		'city' => $customer['city'] ?? '',
		'country_code' => $customer['country_code'] ?? '',
		'zone_code' => $customer['zone_code'] ?? '',
		'phone' => $customer['phone'] ?? '',
		'email' => $customer['email'] ?? '',
		'default_billing_address_id' => $customer['default_billing_address_id'] ?? '',
		'default_shipping_address_id' => $customer['default_shipping_address_id'] ?? '',
	];

	ob_clean();
	header('Content-type: application/json; charset='. mb_http_output());
	echo f::format_json($json);
	exit;
