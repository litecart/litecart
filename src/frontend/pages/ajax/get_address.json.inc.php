<?php

	header('X-Robots-Tag: noindex');

	try {

		if (empty($_GET['trigger'])) {
			throw new Exception('Invalid trigger', 400);
		}

		$customer = new mod_customer();

		$address = $customer->get_address(array_merge($_POST, $_GET));

		if (!$address) {
			throw new Exception('Unable to find address', 404);
		}

		if (!empty($address['error'])) {
			throw new Exception($address['error'], 400);
		}

		$result = [
			'tax_id' => $address['tax_id'] ?? '',
			'company' => $address['company'] ?? '',
			'firstname' => $address['firstname'] ?? '',
			'lastname' => $address['lastname'] ?? '',
			'address1' => $address['address1'] ?? '',
			'address2' => $address['address2'] ?? '',
			'postcode' => $address['postcode'] ?? '',
			'city' => $address['city'] ?? '',
			'country_code' => $address['country_code'] ?? '',
			'zone_code' => $address['zone_code'] ?? '',
			'phone' => $address['phone'] ?? '',
			'email' => $address['email'] ?? '',
			'alert' => $address['alert'] ?? '',
		];

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-type: text/plain; charset='. mb_http_output());
	echo f::format_json($result);
	exit;
