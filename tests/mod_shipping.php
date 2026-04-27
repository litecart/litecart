<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Save auto increment IDs
		$geo_zones_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."geo_zones';"
		)->fetch('Auto_increment');

		$zones_to_geo_zones_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."zones_to_geo_zones';"
		)->fetch('Auto_increment');

		$modules_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."modules';"
		)->fetch('Auto_increment');

		database::query("start transaction;");

		// Create test geo zone for DE
		database::query(
			"insert into ". DB_TABLE_PREFIX ."geo_zones
			(code, name) values ('TEST_SHIP', 'Test Shipping Zone');"
		);
		$geo_zone_id = database::insert_id();

		database::query(
			"insert into ". DB_TABLE_PREFIX ."zones_to_geo_zones
			(geo_zone_id, country_code, zone_code, city)
			values (". (int)$geo_zone_id .", 'DE', '', '');"
		);

		########################################################################
		## sm_zone_weight — direct module test with fallback zone
		########################################################################

		$module = new sm_zone_weight();
		$module->settings = [
			'status' => '1',
			'icon' => '',
			'weight_unit' => 'kg',
			'geo_zone_id_1' => '',
			'weight_rate_table_1' => '',
			'geo_zone_id_2' => '',
			'weight_rate_table_2' => '',
			'geo_zone_id_3' => '',
			'weight_rate_table_3' => '',
			'weight_rate_table_x' => '0:5.00;5:8.95;10:15.95;20:25.00',
			'method' => '>=',
			'handling_fee' => '0',
			'tax_class_id' => '0',
		];

		// Simulate cart items: 2 items, 3kg total
		$items = [
			['quantity' => 1, 'weight' => 2, 'weight_unit' => 'kg', 'price' => 29.99],
			['quantity' => 1, 'weight' => 1, 'weight_unit' => 'kg', 'price' => 19.99],
		];

		$address = ['country_code' => 'US', 'zone_code' => '', 'city' => ''];

		$options = $module->options($items, 49.98, 0, currency::$selected['code'], $address);

		if (empty($options)) {
			throw new Exception('sm_zone_weight: Fallback zone should return options for non-matched country');
		}

		// 3kg total, method >= : 0:5.00 matches (3 >= 0), then 5:8.95 does NOT match (3 < 5)
		// So fee should be 5.00
		$fee = (float)$options[0]['cost'];

		if (abs($fee - 5.00) > 0.01) {
			throw new Exception('sm_zone_weight: Expected fee 5.00 for 3kg (fallback zone), got '. $fee);
		}

		########################################################################
		## sm_zone_weight — heavier package
		########################################################################

		$heavy_items = [
			['quantity' => 1, 'weight' => 12, 'weight_unit' => 'kg', 'price' => 99.99],
		];

		$options = $module->options($heavy_items, 99.99, 0, currency::$selected['code'], $address);

		// 12kg, method >= : 0:5.00, 5:8.95, 10:15.95 all match, last wins → 15.95
		$fee = (float)$options[0]['cost'];

		if (abs($fee - 15.95) > 0.01) {
			throw new Exception('sm_zone_weight: Expected fee 15.95 for 12kg, got '. $fee);
		}

		########################################################################
		## sm_zone_weight — with geo zone match
		########################################################################

		$module->settings['geo_zone_id_1'] = $geo_zone_id;
		$module->settings['weight_rate_table_1'] = '0:3.00;5:6.00;10:9.00';

		$de_address = ['country_code' => 'DE', 'zone_code' => '', 'city' => ''];

		$options = $module->options($items, 49.98, 0, currency::$selected['code'], $de_address);

		if (empty($options)) {
			throw new Exception('sm_zone_weight: Zone 1 should match for DE address');
		}

		// 3kg, zone 1 table: 0:3.00 matches → fee 3.00
		$fee = (float)$options[0]['cost'];

		if (abs($fee - 3.00) > 0.01) {
			throw new Exception('sm_zone_weight: Expected fee 3.00 for 3kg in zone 1, got '. $fee);
		}

		########################################################################
		## sm_zone_weight — handling fee
		########################################################################

		$module->settings['handling_fee'] = '2.50';
		$module->settings['geo_zone_id_1'] = '';
		$module->settings['weight_rate_table_1'] = '';

		$options = $module->options($items, 49.98, 0, currency::$selected['code'], $address);

		// Fallback zone: 3kg → 5.00 + handling 2.50 = 7.50
		$fee = (float)$options[0]['cost'];

		if (abs($fee - 7.50) > 0.01) {
			throw new Exception('sm_zone_weight: Expected fee 7.50 (5.00 + 2.50 handling) for 3kg, got '. $fee);
		}

		########################################################################
		## sm_zone_weight — disabled module returns null
		########################################################################

		$module->settings['status'] = '0';

		$options = $module->options($items, 49.98, 0, currency::$selected['code'], $address);

		if ($options !== null) {
			throw new Exception('sm_zone_weight: Disabled module should return null');
		}

		########################################################################
		## sm_zone_weight — empty rate table returns null
		########################################################################

		$module->settings['status'] = '1';
		$module->settings['weight_rate_table_x'] = '';
		$module->settings['handling_fee'] = '0';

		$options = $module->options($items, 49.98, 0, currency::$selected['code'], $address);

		if ($options !== null) {
			throw new Exception('sm_zone_weight: Empty fallback rate table should return null');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::query('rollback;');

		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."geo_zones AUTO_INCREMENT = ". (int)$geo_zones_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."zones_to_geo_zones AUTO_INCREMENT = ". (int)$zones_to_geo_zones_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."modules AUTO_INCREMENT = ". (int)$modules_auto_id .";");
	}
