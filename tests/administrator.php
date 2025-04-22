<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."administrators';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Prepare some example data
		$data = [
			'status' => 1,
			'username' => 'test',
			'email' => 'test@example.com',
			'two_factor_auth' => 1,
			'date_valid_from' => '2023-01-01 00:00:00',
			'date_valid_to' => '2023-12-31 23:59:59',
		];

		$password = '123456';

		########################################################################
		## Creating a new administrator
		########################################################################

		// Create a new entity
		$administrator = new ent_administrator();

		// Set data
		$administrator->data = functions::array_update($administrator->data, $data);

		$administrator->set_password('123456');

		// Save changes to database
		$administrator->save();

		// Check if the entity was created
		if (!$administrator_id = $administrator->data['id']) {
			throw new Exception('Failed to create administrator');
		}

		########################################################################
		## Load and update the administrator
		########################################################################

		// Load the entity
		$administrator = new ent_administrator($administrator_id);

		// Check if the administrator was loaded
		if ($administrator->data['id'] != $administrator_id) {
			throw new Exception('Failed to load administrator');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $administrator->data)) {
			throw new Exception('The administrator data was not stored correctly');
		}

		// Check if the password was stored correctly
		if (!password_verify($password, $administrator->data['password_hash'])) {
			throw new Exception('The administrator password was not stored correctly');
		}

		########################################################################
		## Updating the administrator
		########################################################################

		// Prepare some new data
		$data = [
			'status' => 0,
			'username' => 'test2',
			'email' => 'test2@example.com',
			'two_factor_auth' => 0,
			'date_valid_from' => '2024-01-01 00:00:00',
			'date_valid_to' => '2024-12-31 23:59:59',
		];

		// Update some data
		$administrator->data = functions::array_update($administrator->data, $data);

		// Set a new password
		$administrator->set_password($password = '654321');

		// Save changes to database
		$administrator->save();

		// Reload the entity
		$administrator = new ent_administrator($administrator_id);

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $administrator->data)) {
			throw new Exception('The administrator data was not updated correctly');
		}

		// Check if the password was stored correctly
		if (!password_verify($password, $administrator->data['password_hash'])) {
			throw new Exception('The administrator password was not updated correctly');
		}

		########################################################################
		## Deleting the administrator
		########################################################################

		// Delete the entity
		$administrator->delete();

		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."administrators
			where id = ". (int)$administrator_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete administrator');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."administrators AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
