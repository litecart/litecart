<?php

	$customer = database::query(
		"select * from ". DB_TABLE_PREFIX ."customers
		where id = '". database::input($_REQUEST['customer_id']) ."'
		limit 1;"
	)->fetch();

	if (!$customer) exit;

	$json = [
		'tax_id' => fallback(null, $customer['tax_id']),
		'company' => fallback(null, $customer['company']),
		'firstname' => fallback(null, $customer['firstname']),
		'lastname' => fallback(null, $customer['lastname']),
		'address1' => fallback(null, $customer['address1']),
		'address2' => fallback(null, $customer['address2']),
		'postcode' => fallback(null, $customer['postcode']),
		'city' => fallback(null, $customer['city']),
		'country_code' => fallback(null, $customer['country_code']),
		'zone_code' => fallback(null, $customer['zone_code']),
		'phone' => fallback(null, $customer['phone']),
		'email' => fallback(null, $customer['email']),
		'default_billing_address_id' => fallback(null, $customer['default_billing_address_id']),
		'default_shipping_address_id' => fallback(null, $customer['default_shipping_address_id']),
	];

	ob_clean();
	header('Content-type: application/json; charset='. mb_http_output());
	echo f::format_json($json);
	exit;
