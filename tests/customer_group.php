<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."customer_groups';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Prepare some example data
		$data = [
			'name' => 'VIP Customers',
			'description' => 'Customers with VIP status',
			'discount' => 10,
			'priority' => 1,
		];

		########################################################################
		## Creating a new customer group
		########################################################################

		// Create a new entity
		$customer_group = new ent_customer_group();
		$customer_group->data = functions::array_update($customer_group->data, $data);
		$customer_group->save();

		// Check if the entity was created
		if (!$customer_group_id = $customer_group->data['id']) {
			throw new Exception('Failed to create customer group');
		}

		########################################################################
		## Load and check the customer group
		########################################################################

		// Load the entity
		$customer_group = new ent_customer_group($customer_group_id);

		// Check if the customer group was loaded
		if ($customer_group->data['id'] != $customer_group_id) {
			throw new Exception('Failed to load customer group');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $customer_group->data)) {
			throw new Exception('The customer group data was not stored correctly');
		}

		########################################################################
		## Updating the customer group
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Premium Customers',
			'description' => 'Customers with premium status',
			'discount' => 15,
			'priority' => 2,
		];

		// Update some data
		$customer_group->data = functions::array_update($customer_group->data, $data);

		// Save changes to database
		$customer_group->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $customer_group->data)) {
			throw new Exception('The customer group data was not updated correctly');
		}

		########################################################################
		## Deleting the customer group
		########################################################################

		// Delete the entity
		$customer_group->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."customer_groups
			where id = ". (int)$customer_group_id ."
			limit 1;"
		)->num_rows() > 0) {
			throw new Exception('Failed to delete customer group');
		}

		return true;

	} catch (Exception $e) {

		echo 'Test failed: '. $e->getMessage();
		return false;

	} finally {

		// Rollback changes to the database
		database::query('rollback;');

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."customer_groups AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
