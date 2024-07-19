<?php

	if (empty($_GET['trigger'])) die('{}');

	$customer = new mod_customer();

	$result = $customer->get_address(array_merge($_POST, $_GET));

	if (empty($result)) die('{}');

	if (!empty($result['error'])) die('{}');

	$json = [
		'tax_id' => fallback($result['tax_id']),
		'company' => fallback($result['company']),
		'firstname' => fallback($result['firstname']),
		'lastname' => fallback($result['lastname']),
		'address1' => fallback($result['address1']),
		'address2' => fallback($result['address2']),
		'postcode' => fallback($result['postcode']),
		'city' => fallback($result['city']),
		'country_code' => fallback($result['country_code']),
		'zone_code' => fallback($result['zone_code']),
		'phone' => fallback($result['phone']),
		'email' => fallback($result['email']),
		'alert' => fallback($result['alert']),
	];

	ob_clean();
	header('Content-type: text/plain; charset='. mb_http_output());
	echo json_encode($json, JSON_UNESCAPED_SLASHES);
	exit;
