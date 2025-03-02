<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."currencies';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Prepare some example data
		$data = [
			'name' => 'Test Currency',
			'code' => 'XYZ',
			'value' => 1.23,
			'prefix' => '$',
			'suffix' => '',
			'decimals' => 2,
			'priority' => 1,
		];

		########################################################################
		## Creating a new currency
		########################################################################

		// Create a new entity
		$currency = new ent_currency();
		$currency->data = functions::array_update($currency->data, $data);
		$currency->save();

		// Check if the entity was created
		if (!$currency_id = $currency->data['id']) {
			throw new Exception('Failed to create currency');
		}

		########################################################################
		## Load and check the currency
		########################################################################

		// Load the entity
		$currency = new ent_currency($currency_id);

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $currency->data)) {
			throw new Exception('The currency data was not stored correctly');
		}

		########################################################################
		## Update the currency
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Updated Currency',
			'code' => 'XYZ',
			'value' => 4.56,
			'prefix' => '€',
			'suffix' => '',
			'decimals' => 2,
			'priority' => 2,
		];

		// Update some data
		$currency->data = functions::array_update($currency->data, $data);

		// Save changes to database
		$currency->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $currency->data)) {
			throw new Exception('The currency data was not updated correctly');
		}

		########################################################################
		## Deleting the currency
		########################################################################

		// Delete the entity
		$currency->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."currencies
			where id = ". (int)$currency_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete currency');
		}

		echo '  Test passed successfully!' . PHP_EOL;
		return true;

	} catch (Exception $e) {

		echo 'Test failed: '. $e->getMessage();
		return false;

	} finally {

		// Rollback changes to the database
		database::query('rollback;');

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."currencies AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
