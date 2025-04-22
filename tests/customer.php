<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."customers';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction - so we can rollback the changes
		database::query("start transaction;");

		// Define some example data
		$data = [
			'status' => 1,
			'firstname' => 'John',
			'lastname' => 'Doe',
			'email' => 'john.doe@example.com',
			'phone' => '123456789',
			'password' => 'password123',
		];

		########################################################################
		## Creating a new customer
		########################################################################

		$customer = new ent_customer();
		$customer->data = functions::array_update($customer->data, $data);
		$customer->save();

		if (!$customer_id = $customer->data['id']) {
			throw new Exception('Failed to create customer');
		}

		########################################################################
		## Load and check the customer
		########################################################################

		$customer = new ent_customer($customer_id);

		if ($customer->data['id'] != $customer_id) {
			throw new Exception('Failed to load customer');
		}

		if (!functions::array_intersect_compare($data, $customer->data)) {
			throw new Exception('The customer data was not stored correctly');
		}

		// Define some example data
		$data = [
			'status' => 0,
			'firstname' => 'Jane',
			'lastname' => 'Smith',
			'email' => 'jane.smith@example.com',
			'phone' => '987654321',
			'password' => 'newpassword123',
		];

		$customer->data = functions::array_update($customer->data, $data);

		$customer->save();

		if (!functions::array_intersect_compare($data, $customer->data)) {
			throw new Exception('The customer data was not updated correctly');
		}

		########################################################################
		## Delete the customer
		########################################################################

		$customer->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."customers
			where id = ". (int)$customer_id ."
			limit 1;"
		)->num_rows() > 0) {
			throw new Exception('Failed to delete customer');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		// Rollback changes to the database
		database::query('rollback;');

		// Reset the auto increment
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."customers AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
