<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."stock_transactions';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Prepare some example data
		$data = [
			'product_id' => 1,
			'quantity' => 10,
			'date' => '2023-10-01',
			'comments' => 'Initial stock',
		];

		########################################################################
		## Creating a new stock transaction
		########################################################################

		// Create a new entity
		$stock_transaction = new ent_stock_transaction();
		$stock_transaction->data = functions::array_update($stock_transaction->data, $data);
		$stock_transaction->save();

		// Check if the entity was created
		if (!$stock_transaction_id = $stock_transaction->data['id']) {
			throw new Exception('Failed to create stock transaction');
		}

		########################################################################
		## Load and check the stock transaction
		########################################################################

		// Load the entity
		$stock_transaction = new ent_stock_transaction($stock_transaction_id);

		// Check if the stock transaction was loaded
		if ($stock_transaction->data['id'] != $stock_transaction_id) {
			throw new Exception('Failed to load stock transaction');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $stock_transaction->data)) {
			throw new Exception('The stock transaction data was not stored correctly');
		}

		########################################################################
		## Updating the stock transaction
		########################################################################

		// Prepare some new data
		$data = [
			'quantity' => 20,
			'comments' => 'Updated stock',
		];

		// Update some data
		foreach ($data as $key => $value) {
			$stock_transaction->data[$key] = $value;
		}

		// Save changes to database
		$stock_transaction->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $stock_transaction->data)) {
			throw new Exception('The stock transaction data was not updated correctly');
		}

		########################################################################
		## Deleting the stock transaction
		########################################################################

		// Delete the entity
		$stock_transaction->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."stock_transactions
			where id = ". (int)$stock_transaction_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete stock transaction');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."stock_transactions AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
