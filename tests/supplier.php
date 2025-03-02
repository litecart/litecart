<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."suppliers';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Prepare some example data
		$data = [
			'name' => 'Supplier A',
			'description' => 'Description for Supplier A',
			'email' => 'supplierA@example.com',
			'phone' => '123456789',
			'address' => '123 Supplier St.',
			'city' => 'Supplier City',
			'country_code' => 'US',
			'zone_code' => 'CA',
		];

		########################################################################
		## Creating a new supplier
		########################################################################

		// Create a new entity
		$supplier = new ent_supplier();
		$supplier->data = functions::array_update($supplier->data, $data);
		$supplier->save();

		// Check if the entity was created
		if (!$supplier_id = $supplier->data['id']) {
			throw new Exception('Failed to create supplier');
		}

		########################################################################
		## Load and check the supplier
		########################################################################

		// Load the entity
		$supplier = new ent_supplier($supplier_id);

		// Check if the supplier was loaded
		if ($supplier->data['id'] != $supplier_id) {
			throw new Exception('Failed to load supplier');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $supplier->data)) {
			throw new Exception('The supplier data was not stored correctly');
		}

		########################################################################
		## Updating the supplier
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Supplier B',
			'description' => 'Description for Supplier B',
			'email' => 'supplierB@example.com',
			'phone' => '987654321',
			'address' => '456 Supplier Ave.',
			'city' => 'Supplier Town',
			'country_code' => 'US',
			'zone_code' => 'NY',
		];

		// Update some data
		foreach ($data as $key => $value) {
			$supplier->data[$key] = $value;
		}

		// Save changes to database
		$supplier->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $supplier->data)) {
			throw new Exception('The supplier data was not updated correctly');
		}

		########################################################################
		## Deleting the supplier
		########################################################################

		// Delete the entity
		$supplier->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."suppliers
			where id = ". (int)$supplier_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete supplier');
		}

		echo '  Test passed successfully!' . PHP_EOL;
		return true;

	} catch (Exception $e) {
		echo 'Test failed: '. $e->getMessage();

	} finally {

		// Rollback changes to the database
		database::query('rollback;');

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."suppliers AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
