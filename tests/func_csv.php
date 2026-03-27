<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## csv_encode — basic comma-separated
		########################################################################

		$data = [
			['name' => 'Product A', 'price' => '9.99'],
			['name' => 'Product B', 'price' => '19.99'],
		];

		$csv = f::csv_encode($data);

		if (strpos($csv, 'name') === false || strpos($csv, 'price') === false) {
			throw new Exception('csv_encode missing column headers');
		}

		if (strpos($csv, 'Product A') === false || strpos($csv, '9.99') === false) {
			throw new Exception('csv_encode missing row data');
		}

		########################################################################
		## csv_encode — tab delimiter
		########################################################################

		$csv_tab = f::csv_encode($data, "\t");

		if (strpos($csv_tab, "\t") === false) {
			throw new Exception('csv_encode with tab delimiter missing tabs');
		}

		########################################################################
		## csv_encode — enclosure for special characters
		########################################################################

		$data_special = [
			['name' => 'Product, with comma', 'note' => 'has "quotes"'],
		];

		$csv_special = f::csv_encode($data_special);

		if (strpos($csv_special, '"Product, with comma"') === false) {
			throw new Exception('csv_encode failed to enclose value containing delimiter');
		}

		########################################################################
		## csv_decode — roundtrip
		########################################################################

		$original = [
			['name' => 'Alpha', 'value' => '100'],
			['name' => 'Beta', 'value' => '200'],
		];

		$encoded = f::csv_encode($original, ',', '"', '"', 'utf-8', "\n");
		$decoded = f::csv_decode($encoded, ',', '"', '"', 'utf-8');

		if (count($decoded) !== 2) {
			throw new Exception('csv_decode returned wrong row count: '. count($decoded));
		}

		if ($decoded[0]['name'] !== 'Alpha' || $decoded[1]['value'] !== '200') {
			throw new Exception('csv_decode data mismatch after roundtrip');
		}

		########################################################################
		## csv_decode — auto-detect delimiter
		########################################################################

		$tsv_string = "name\tprice\nWidget\t5.99\n";
		$decoded = f::csv_decode($tsv_string);

		if ($decoded[0]['name'] !== 'Widget') {
			throw new Exception('csv_decode auto-detect failed for tab delimiter');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
