<?php

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
			'tax_id' => fallback($address['tax_id']),
			'company' => fallback($address['company']),
			'firstname' => fallback($address['firstname']),
			'lastname' => fallback($address['lastname']),
			'address1' => fallback($address['address1']),
			'address2' => fallback($address['address2']),
			'postcode' => fallback($address['postcode']),
			'city' => fallback($address['city']),
			'country_code' => fallback($address['country_code']),
			'zone_code' => fallback($address['zone_code']),
			'phone' => fallback($address['phone']),
			'email' => fallback($address['email']),
			'alert' => fallback($address['alert']),
		];

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-type: text/plain; charset='. mb_http_output());
	echo functions::json_format($result);
	exit;
