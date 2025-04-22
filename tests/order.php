<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query(
			"start transaction;"
		);

		// Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."orders';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'customer' => [
				'firstname' => 'John',
				'lastname' => 'Doe',
				'email' => 'john.doe@example.com',
				'phone' => '123456789',
				'address1' => '123 Main St',
				'city' => 'Anytown',
				'country_code' => 'US',
				'shipping_address' => [
					'firstname' => 'John',
					'lastname' => 'Doe',
					'address1' => '123 Main St',
					'city' => 'Anytown',
					'country_code' => 'US',
				],
			],
			'order_status_id' => 1,
			'currency_code' => 'USD',
			'currency_value' => 1.0,
		];

		$items = [
			[
				'product_id' => 1,
				'name' => 'Test Product',
				'quantity' => 1,
				'price' => 100.00,
				'tax' => 10.00,
				'weight' => 1.0,
				'weight_unit' => 'kg',
			],
		];

		########################################################################
		## Creating a new order
		########################################################################

		// Create a new entity
		$order = new ent_order();
		$order->data = functions::array_update($order->data, $data);
		foreach ($items as $item) $order->add_item($item);
		$order->save();

		// Check if the entity was created
		if (!$order_id = $order->data['id']) {
			throw new Exception('Failed to create order');
		}

		########################################################################
		## Load and check the order
		########################################################################

		// Load the entity
		$order = new ent_order($order_id);

		// Check if the order was loaded
		if ($order->data['id'] != $order_id) {
			throw new Exception('Failed to load order');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $order->data)) {
			throw new Exception('The order data was not stored correctly');
		}

		########################################################################
		## Updating the order
		########################################################################

		// Prepare some new data
		$data = [
			'customer' => [
				'firstname' => 'Jane',
				'lastname' => 'Doe',
				'email' => 'jane.doe@example.com',
				'phone' => '987654321',
				'address1' => '456 Main St',
				'city' => 'Othertown',
				'country_code' => 'US',
				'shipping_address' => [
					'firstname' => 'Jane',
					'lastname' => 'Doe',
					'address1' => '456 Main St',
					'city' => 'Othertown',
					'country_code' => 'US',
				],
			],
			'order_status_id' => 2,
		];

		// Update some data
		$order->data = functions::array_update($order->data, $data);

		// Save changes to database
		$order->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $order->data)) {
			throw new Exception('The order data was not updated correctly');
		}

		########################################################################
		## Deleting the order
		########################################################################

		// Delete the entity
		$order->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."orders
			where id = ". (int)$order_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete order');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."orders
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
