<?php

	try {

		$customer = [
			'tax_id' => $_REQUEST['tax_id'] ?? '',
			'company' => $_REQUEST['company'] ?? '',
			'country_code' => $_REQUEST['country_code'] ?? settings::get('store_country_code'),
			'zone_code' => $_REQUEST['zone_code'] ?? settings::get('store_zone_code'),
			'city' => $_REQUEST['city'] ?? '',
			'shipping_address' => [
				'tax_id' => $_REQUEST['shipping_address']['tax_id'] ?? $_REQUEST['tax_id'] ?? '',
				'company' => $_REQUEST['shipping_address']['company'] ?? $_REQUEST['company'] ?? '',
				'country_code' => $_REQUEST['shipping_address']['country_code'] ?? $_REQUEST['country_code'] ?? '',
				'zone_code' => $_REQUEST['shipping_address']['zone_code'] ?? $_REQUEST['zone_code'] ?? '',
				'city' => $_REQUEST['shipping_address']['city'] ?? $_REQUEST['city'] ?? '',
			],
		];

		$result = database::query(
			"select * from ". DB_PREFIX ."tax_classes
			order by code, name;"
		)->fetch_all(function($tax_class) use ($customer) {
			return tax::get_rates($tax_class['id'], $customer);
		});

	} catch (Exception $e) {
		http_response_code($e->getCode());
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-Type: application/json');
	echo f::format_json($result);
	exit;
