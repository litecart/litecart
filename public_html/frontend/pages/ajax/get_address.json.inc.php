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
			'tax_id' => fallback(null, $address['tax_id']),
			'company' => fallback(null, $address['company']),
			'firstname' => fallback(null, $address['firstname']),
			'lastname' => fallback(null, $address['lastname']),
			'address1' => fallback(null, $address['address1']),
			'address2' => fallback(null, $address['address2']),
			'postcode' => fallback(null, $address['postcode']),
			'city' => fallback(null, $address['city']),
			'country_code' => fallback(null, $address['country_code']),
			'zone_code' => fallback(null, $address['zone_code']),
			'phone' => fallback(null, $address['phone']),
			'email' => fallback(null, $address['email']),
			'alert' => fallback(null, $address['alert']),
		];

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-type: text/plain; charset='. mb_http_output());
	echo f::format_json($result);
	exit;
