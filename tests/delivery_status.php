<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."delivery_statuses';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'name' => [
				'en' => '4 weeks',
				'fr' => '4 semaines',
			],
			'icon' => 'fa-clock',
			'color' => '#FFA500',
			'priority' => 1,
		];

		########################################################################
		## Creating a new delivery status
		########################################################################

		// Create a new entity
		$delivery_status = new ent_delivery_status();
		$delivery_status->data = functions::array_update($delivery_status->data, $data);
		$delivery_status->save();

		// Check if the entity was created
		if (!$delivery_status_id = $delivery_status->data['id']) {
			throw new Exception('Failed to create delivery status');
		}

		########################################################################
		## Load and check the delivery status
		########################################################################

		// Load the entity
		$delivery_status = new ent_delivery_status($delivery_status_id);

		// Check if the delivery status was loaded
		if ($delivery_status->data['id'] != $delivery_status_id) {
			throw new Exception('Failed to load delivery status');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $delivery_status->data)) {
			throw new Exception('The delivery status data was not stored correctly');
		}

		########################################################################
		## Updating the delivery status
		########################################################################

		// Prepare some new data
		$data = [
			'name' => [
				'en' => '6 weeks',
				'fr' => '6 semaines',
			],
			'icon' => 'fa-truck',
			'color' => '#008000',
			'priority' => 2,
		];

		// Update some data
		$delivery_status->data = functions::array_update($delivery_status->data, $data);

		// Save changes to database
		$delivery_status->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $delivery_status->data)) {
			throw new Exception('The delivery status data was not updated correctly');
		}

		########################################################################
		## Deleting the delivery status
		########################################################################

		// Delete the entity
		$delivery_status->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."delivery_statuses
			where id = ". (int)$delivery_status_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete delivery status');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."delivery_statuses
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);

		// Rollback changes to the database
		database::query("rollback;");
	}
