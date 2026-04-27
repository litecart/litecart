<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Save auto increment IDs for rollback
		$geo_zones_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."geo_zones';"
		)->fetch('Auto_increment');

		$zones_to_geo_zones_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."zones_to_geo_zones';"
		)->fetch('Auto_increment');

		$tax_classes_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."tax_classes';"
		)->fetch('Auto_increment');

		$tax_rates_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."tax_rates';"
		)->fetch('Auto_increment');

		database::query("start transaction;");

		// Create test fixtures: geo zone + zone mapping + tax class + tax rate

		database::query(
			"insert into ". DB_TABLE_PREFIX ."geo_zones
			(code, name) values ('TEST', 'Test Tax Zone');"
		);
		$geo_zone_id = database::insert_id();

		database::query(
			"insert into ". DB_TABLE_PREFIX ."zones_to_geo_zones
			(geo_zone_id, country_code, zone_code, city)
			values (". (int)$geo_zone_id .", 'DE', '', '');"
		);

		database::query(
			"insert into ". DB_TABLE_PREFIX ."tax_classes
			(code, name) values ('test_standard', 'Test Standard Tax');"
		);
		$tax_class_id = database::insert_id();

		database::query(
			"insert into ". DB_TABLE_PREFIX ."tax_rates
			(tax_class_id, geo_zone_id, code, name, rate, address_type,
			 rule_companies_with_tax_id, rule_companies_without_tax_id,
			 rule_individuals_with_tax_id, rule_individuals_without_tax_id)
			values (". (int)$tax_class_id .", ". (int)$geo_zone_id .", 'DE_VAT', 'DE VAT 19%', 19.00, 'payment',
			 0, 1, 1, 1);"
		);

		########################################################################
		## get_tax — basic tax calculation
		########################################################################

		$customer = [
			'tax_id' => '',
			'company' => '',
			'country_code' => 'DE',
			'zone_code' => '',
			'city' => '',
		];

		$tax = tax::get_tax(100, $tax_class_id, $customer);

		if (abs($tax - 19.00) > 0.01) {
			throw new Exception('get_tax: Expected 19.00 for 100 at 19%, got '. $tax);
		}

		########################################################################
		## get_tax — zero value
		########################################################################

		$tax = tax::get_tax(0, $tax_class_id, $customer);

		if ($tax !== 0) {
			throw new Exception('get_tax: Expected 0 for zero value, got '. var_export($tax, true));
		}

		########################################################################
		## get_tax — zero tax class
		########################################################################

		$tax = tax::get_tax(100, 0, $customer);

		if ($tax !== 0) {
			throw new Exception('get_tax: Expected 0 for zero tax class, got '. var_export($tax, true));
		}

		########################################################################
		## get_price — with tax
		########################################################################

		$price = tax::get_price(100, $tax_class_id, true, $customer);

		if (abs($price - 119.00) > 0.01) {
			throw new Exception('get_price: Expected 119.00 incl tax, got '. $price);
		}

		########################################################################
		## get_price — without tax
		########################################################################

		$price = tax::get_price(100, $tax_class_id, false, $customer);

		if (abs($price - 100.00) > 0.01) {
			throw new Exception('get_price: Expected 100.00 excl tax, got '. $price);
		}

		########################################################################
		## get_rates — individual without tax ID (should match)
		########################################################################

		$rates = tax::get_rates($tax_class_id, $customer);

		if (empty($rates)) {
			throw new Exception('get_rates: Expected tax rates for DE individual, got empty');
		}

		if (abs($rates[0]['rate'] - 19.00) > 0.01) {
			throw new Exception('get_rates: Expected rate 19.00, got '. $rates[0]['rate']);
		}

		########################################################################
		## get_rates — company with tax ID (should NOT match, rule=0)
		########################################################################

		$customer_with_taxid = [
			'tax_id' => 'DE123456789',
			'company' => 'Test GmbH',
			'country_code' => 'DE',
			'zone_code' => '',
			'city' => '',
		];

		$rates = tax::get_rates($tax_class_id, $customer_with_taxid);

		if (!empty($rates)) {
			throw new Exception('get_rates: Company with tax ID should be tax-exempt, got '. count($rates) .' rate(s)');
		}

		########################################################################
		## get_rates — wrong country (should NOT match)
		########################################################################

		$customer_us = [
			'tax_id' => '',
			'company' => '',
			'country_code' => 'US',
			'zone_code' => '',
			'city' => '',
		];

		$rates = tax::get_rates($tax_class_id, $customer_us);

		if (!empty($rates)) {
			throw new Exception('get_rates: US customer should not match DE tax zone, got '. count($rates) .' rate(s)');
		}

		########################################################################
		## get_rates — empty tax class ID
		########################################################################

		$rates = tax::get_rates(0, $customer);

		if (!empty($rates)) {
			throw new Exception('get_rates: Empty tax class should return empty array');
		}

		########################################################################
		## get_rates — caching works
		########################################################################

		$rates1 = tax::get_rates($tax_class_id, $customer);
		$rates2 = tax::get_rates($tax_class_id, $customer);

		if ($rates1 !== $rates2) {
			throw new Exception('get_rates: Cached result should be identical');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::query('rollback;');

		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."geo_zones AUTO_INCREMENT = ". (int)$geo_zones_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."zones_to_geo_zones AUTO_INCREMENT = ". (int)$zones_to_geo_zones_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."tax_classes AUTO_INCREMENT = ". (int)$tax_classes_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."tax_rates AUTO_INCREMENT = ". (int)$tax_rates_auto_id .";");
	}
