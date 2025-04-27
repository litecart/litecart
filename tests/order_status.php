<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."order_statuses';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'name' => ['en' => 'Pending'],
			'description' => ['en' => 'Order is pending'],
		];

		########################################################################
		## Creating a new order status
		########################################################################

		// Create a new entity
		$order_status = new ent_order_status();
		$order_status->data = functions::array_update($order_status->data, $data);
		$order_status->save();

		// Check if the entity was created
		if (!$order_status_id = $order_status->data['id']) {
			throw new Exception('Failed to create order status');
		}

		########################################################################
		## Load and check the order status
		########################################################################

		// Load the entity
		$order_status = new ent_order_status($order_status_id);

		// Check if the order status was loaded
		if ($order_status->data['id'] != $order_status_id) {
			throw new Exception('Failed to load order status');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $order_status->data)) {
			throw new Exception('The order status data was not stored correctly');
		}

		########################################################################
		## Updating the order status
		########################################################################

		// Prepare some new data
		$data = [
			'name' => ['en' => 'Shipped'],
			'description' => ['en' => 'Order has been shipped'],
		];

		// Update some data
		$order_status->data = functions::array_update($order_status->data, $data);

		// Save changes to database
		$order_status->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $order_status->data)) {
			throw new Exception('The order status data was not updated correctly');
		}

		########################################################################
		## Deleting the order status
		########################################################################

		// Delete the entity
		$order_status->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."order_statuses
			where id = ". (int)$order_status_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete order status');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		// Rollback changes to the database
		database::query("rollback;");

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."order_statuses
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
