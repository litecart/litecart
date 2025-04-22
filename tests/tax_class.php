<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."tax_classes';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Prepare some example data
		$data = [
			'name' => 'Standard Tax Class',
			'description' => 'Standard tax class for products',
		];

		########################################################################
		## Creating a new tax class
		########################################################################

		// Create a new entity
		$tax_class = new ent_tax_class();
		$tax_class->data = functions::array_update($tax_class->data, $data);
		$tax_class->save();

		// Check if the entity was created
		if (!$tax_class_id = $tax_class->data['id']) {
			throw new Exception('Failed to create tax class');
		}

		########################################################################
		## Load and check the tax class
		########################################################################

		// Load the entity
		$tax_class = new ent_tax_class($tax_class_id);

		// Check if the tax class was loaded
		if ($tax_class->data['id'] != $tax_class_id) {
			throw new Exception('Failed to load tax class');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $tax_class->data)) {
			throw new Exception('The tax class data was not stored correctly');
		}

		########################################################################
		## Updating the tax class
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Reduced Tax Class',
			'description' => 'Reduced tax class for products',
		];

		// Update some data
		foreach ($data as $key => $value) {
			$tax_class->data[$key] = $value;
		}

		// Save changes to database
		$tax_class->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $tax_class->data)) {
			throw new Exception('The tax class data was not updated correctly');
		}

		########################################################################
		## Deleting the tax class
		########################################################################

		// Delete the entity
		$tax_class->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."tax_classes
			where id = ". (int)$tax_class_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete tax class');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		// Rollback changes to the database
		database::query('rollback;');

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."tax_classes AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
