<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."sold_out_statuses';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'name' => [
				'en' => 'Out of Stock',
			],
			'description' => [
				'en' => 'This item is currently out of stock.',
			],
			'orderable' => 0,
		];

		########################################################################
		## Creating a new sold out status
		########################################################################

		// Create a new entity
		$sold_out_status = new ent_sold_out_status();
		$sold_out_status->data = functions::array_update($sold_out_status->data, $data);
		$sold_out_status->save();

		// Check if the entity was created
		if (!$sold_out_status_id = $sold_out_status->data['id']) {
			throw new Exception('Failed to create sold out status');
		}

		########################################################################
		## Load and check the sold out status
		########################################################################

		// Load the entity
		$sold_out_status = new ent_sold_out_status($sold_out_status_id);

		// Check if the sold out status was loaded
		if ($sold_out_status->data['id'] != $sold_out_status_id) {
			throw new Exception('Failed to load sold out status');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $sold_out_status->data)) {
			throw new Exception('The sold out status data was not stored correctly');
		}

		########################################################################
		## Updating the sold out status
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Back in Stock',
			'description' => 'This item is now back in stock.',
			'orderable' => 1,
		];

		// Update some data
		$sold_out_status->data = functions::array_update($sold_out_status->data, $data);

		// Save changes to database
		$sold_out_status->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $sold_out_status->data)) {
			throw new Exception('The sold out status data was not updated correctly');
		}

		########################################################################
		## Deleting the sold out status
		########################################################################

		// Delete the entity
		$sold_out_status->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."sold_out_statuses
			where id = ". (int)$sold_out_status_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete sold out status');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."sold_out_statuses
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
