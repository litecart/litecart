<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."tax_rates';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Prepare some example data
		$data = [
			'name' => 'Standard Rate',
			'code' => 'standard',
			'rate' => 20.00,
			'type' => 'percentage',
			'geo_zone_id' => 1,
			'priority' => 1,
		];

		########################################################################
		## Creating a new tax rate
		########################################################################

		// Create a new entity
		$tax_rate = new ent_tax_rate();
		$tax_rate->data = functions::array_update($tax_rate->data, $data);
		$tax_rate->save();

		// Check if the entity was created
		if (!$tax_rate_id = $tax_rate->data['id']) {
			throw new Exception('Failed to create tax rate');
		}

		########################################################################
		## Load and check the tax rate
		########################################################################

		// Load the entity
		$tax_rate = new ent_tax_rate($tax_rate_id);

		// Check if the tax rate was loaded
		if ($tax_rate->data['id'] != $tax_rate_id) {
			throw new Exception('Failed to load tax rate');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $tax_rate->data)) {
			throw new Exception('The tax rate data was not stored correctly');
		}

		########################################################################
		## Updating the tax rate
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Reduced Rate',
			'code' => 'reduced',
			'rate' => 10.00,
			'type' => 'percentage',
			'geo_zone_id' => 2,
			'priority' => 2,
		];

		// Update some data
		foreach ($data as $key => $value) {
			$tax_rate->data[$key] = $value;
		}

		// Save changes to database
		$tax_rate->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $tax_rate->data)) {
			throw new Exception('The tax rate data was not updated correctly');
		}

		########################################################################
		## Deleting the tax rate
		########################################################################

		// Delete the entity
		$tax_rate->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."tax_rates
			where id = ". (int)$tax_rate_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete tax rate');
		}

		return true;

	} catch (Exception $e) {

		echo 'Test failed: '. $e->getMessage();
		return false;

	} finally {

		// Rollback changes to the database
		database::query("rollback;");

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."tax_rates AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
