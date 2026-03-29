<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## format_json — pretty print
		########################################################################

		$data = ['name' => 'Test', 'value' => 42];
		$json = f::format_json($data);

		if (strpos($json, "\t") === false) {
			throw new Exception('format_json with indent should contain tabs');
		}

		$decoded = json_decode($json, true);
		if ($decoded['name'] !== 'Test' || $decoded['value'] !== 42) {
			throw new Exception('format_json output not valid JSON after decode');
		}

		########################################################################
		## format_json — compact (no indent)
		########################################################################

		$json_compact = f::format_json($data, false);

		if (strpos($json_compact, "\t") !== false || strpos($json_compact, "\n") !== false) {
			throw new Exception('format_json without indent should not contain tabs or newlines');
		}

		########################################################################
		## format_path_friendly — basic slug
		########################################################################

		$slug = f::format_path_friendly('Hello World 123');

		if ($slug !== 'hello-world-123') {
			throw new Exception('format_path_friendly basic failed: got "'. $slug .'"');
		}

		########################################################################
		## format_path_friendly — special characters
		########################################################################

		$slug = f::format_path_friendly('Über Straße & Café');

		if (empty($slug) || strpos($slug, '&') !== false) {
			throw new Exception('format_path_friendly should strip special chars: got "'. $slug .'"');
		}

		########################################################################
		## format_path_friendly — German umlauts
		########################################################################

		$slug = f::format_path_friendly('Ärger mit Öl', 'de');

		if (strpos($slug, 'aerger') === false) {
			throw new Exception('format_path_friendly German should convert Ä to Ae: got "'. $slug .'"');
		}

		########################################################################
		## format_address — basic address formatting
		########################################################################

		$address = [
			'company' => 'ACME Corp',
			'firstname' => 'John',
			'lastname' => 'Doe',
			'address1' => '123 Main St',
			'address2' => '',
			'city' => 'New York',
			'postcode' => '10001',
			'zone_code' => 'NY',
			'country_code' => 'US',
		];

		$formatted = f::format_address($address);

		if (empty($formatted)) {
			throw new Exception('format_address returned empty string');
		}

		if (strpos($formatted, 'John') === false || strpos($formatted, 'Doe') === false) {
			throw new Exception('format_address missing name in output');
		}

		if (strpos($formatted, 'New York') === false) {
			throw new Exception('format_address missing city in output');
		}

		########################################################################
		## format_number — uses language formatting
		########################################################################

		$result = f::format_number(1234.56, 2);

		if (empty($result)) {
			throw new Exception('format_number returned empty');
		}

		// Should contain the digits regardless of separator style
		if (strpos(str_replace(['.', ',', ' '], '', $result), '123456') === false) {
			throw new Exception('format_number missing expected digits: got "'. $result .'"');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
