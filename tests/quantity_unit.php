<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."quantity_units';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'name' => 'Kilogram',
			'description' => 'Unit of mass',
			'decimals' => 3,
		];

		########################################################################
		## Creating a new quantity unit
		########################################################################

		// Create a new entity
		$quantity_unit = new ent_quantity_unit();
		$quantity_unit->data = functions::array_update($quantity_unit->data, $data);
		$quantity_unit->save();

		// Check if the entity was created
		if (!$quantity_unit_id = $quantity_unit->data['id']) {
			throw new Exception('Failed to create quantity unit');
		}

		########################################################################
		## Load and check the quantity unit
		########################################################################

		// Load the entity
		$quantity_unit = new ent_quantity_unit($quantity_unit_id);

		// Check if the quantity unit was loaded
		if ($quantity_unit->data['id'] != $quantity_unit_id) {
			throw new Exception('Failed to load quantity unit');
		}

		// Check if data was set correctly
		foreach ($data as $key => $value) {
			if ($quantity_unit->data[$key] != $value) {
				throw new Exception('The quantity unit data was not stored correctly ('. $key .')');
			}
		}

		########################################################################
		## Updating the quantity unit
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Gram',
			'description' => 'Unit of mass',
			'decimals' => 2,
		];

		// Update some data
		$quantity_unit->data = functions::array_update($quantity_unit->data, $data);

		// Save changes to database
		$quantity_unit->save();

		// Check if data was set correctly
		foreach ($data as $key => $value) {
			if ($quantity_unit->data[$key] != $value) {
				throw new Exception('The quantity unit data was not updated correctly ('. $key .')');
			}
		}

		########################################################################
		## Deleting the quantity unit
		########################################################################

		// Delete the entity
		$quantity_unit->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."quantity_units
			where id = ". (int)$quantity_unit_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete quantity unit');
		}

		echo '  Test passed successfully!' . PHP_EOL;
		return true;

	} catch (Exception $e) {
		echo 'Test failed: '. $e->getMessage();

	} finally {

		// Rollback changes to the database
		database::query("rollback;");

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."quantity_units
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
