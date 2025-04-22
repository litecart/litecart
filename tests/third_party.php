<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		 // Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."third_parties';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'name' => 'New Third Party',
			'description' => [
				'en' => 'This is a new third party.',
			],
			'collected_data' => [
				'en' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
			],
			'purposes' => [
				'en' => 'Consectetur adipiscing elit. Lorem ipsum dolor sit amet.',
			],
		];

		########################################################################
		## Creating a new third party
		########################################################################

		// Create a new entity
		$third_party = new ent_third_party();
		$third_party->data = functions::array_update($third_party->data, $data);
		$third_party->save();

		// Check if the entity was created
		if (!$third_party_id = $third_party->data['id']) {
			throw new Exception('Failed to create third party');
		}

		########################################################################
		## Load and check the third party
		########################################################################

		// Load the entity
		$third_party = new ent_third_party($third_party_id);

		// Check if the third party was loaded
		if ($third_party->data['id'] != $third_party_id) {
			throw new Exception('Failed to load third party');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $third_party->data)) {
			throw new Exception('The third party data was not stored correctly');
		}

		########################################################################
		## Updating the third party
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Updated Third Party',
			'description' => 'This is an updated third party.',
		];

		// Update some data
		$third_party->data = functions::array_update($third_party->data, $data);

		// Save changes to database
		$third_party->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $third_party->data)) {
			throw new Exception('The third party data was not updated correctly');
		}

		########################################################################
		## Deleting the third party
		########################################################################

		// Delete the entity
		$third_party->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."third_parties
			where id = ". (int)$third_party_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete third party');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."third_parties
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
