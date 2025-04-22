<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."stock_items';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Prepare some example data
		$data = [
			'name' => ['en' => 'Test Stock Item'],
			'quantity' => 100,
			'location' => 'Warehouse A',
		];

		########################################################################
		## Creating a new stock item
		########################################################################

		// Create a new entity
		$stock_item = new ent_stock_item();
		//$stock_item->data = functions::array_update($stock_item->data, $data);
		foreach ($data as $key => $value) {
			$stock_item->data[$key] = $value;
		}

		$stock_item->save();

		// Check if the entity was created
		if (!$stock_item_id = $stock_item->data['id']) {
			throw new Exception('Failed to create stock item');
		}

		########################################################################
		## Load and check the stock item
		########################################################################

		// Load the entity
		$stock_item = new ent_stock_item($stock_item_id);

		// Check if the stock item was loaded
		if ($stock_item->data['id'] != $stock_item_id) {
			throw new Exception('Failed to load stock item');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $stock_item->data)) {
			print_r($data);
			print_r($stock_item->data);
			throw new Exception('The stock item data was not stored correctly');
		}

		########################################################################
		## Updating the stock item
		########################################################################

		// Prepare some new data
		$data = [
			'name' => ['en' => 'Updated Stock Item'],
			'quantity' => 200,
			'location' => 'Warehouse B',
		];

		// Update some data
		//$stock_item->data = functions::array_update($stock_item->data, $data);
		foreach ($data as $key => $value) {
			$stock_item->data[$key] = $value;
		}

		// Save changes to database
		$stock_item->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $stock_item->data)) {
			throw new Exception('The stock item data was not updated correctly');
		}

		########################################################################
		## Deleting the stock item
		########################################################################

		// Delete the entity
		$stock_item->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."stock_items
			where id = ". (int)$stock_item_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete stock item');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."stock_items AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
