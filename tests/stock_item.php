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
			'product_id' => 1,
			'quantity' => 100,
			'location' => 'Warehouse A',
		];

		########################################################################
		## Creating a new stock item
		########################################################################

		// Create a new entity
		$stock_item = new ent_stock_item();
		$stock_item->data = functions::array_update($stock_item->data, $data);
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
		foreach ($data as $key => $value) {
			if ($stock_item->data[$key] != $value) {
				throw new Exception('The stock item data was not stored correctly ('. $key .')');
			}
		}

		########################################################################
		## Updating the stock item
		########################################################################

		// Prepare some new data
		$data = [
			'quantity' => 200,
			'location' => 'Warehouse B',
		];

		// Update some data
		$stock_item->data = functions::array_update($stock_item->data, $data);

		// Save changes to database
		$stock_item->save();

		// Check if data was set correctly
		foreach ($data as $key => $value) {
			if ($stock_item->data[$key] != $value) {
				throw new Exception('The stock item data was not updated correctly ('. $key .')');
			}
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

		echo '  Test passed successfully!' . PHP_EOL;
		return true;

	} catch (Exception $e) {
		echo 'Test failed: '. $e->getMessage();

	} finally {

		// Rollback changes to the database
		database::query('rollback;');

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."stock_items AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
