<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."geo_zones';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'name' => 'Example Geo Zone',
			'description' => 'This is an example geo zone',
			'priority' => 1,
		];

		########################################################################
		## Creating a new geo zone
		########################################################################

		// Create a new entity
		$geo_zone = new ent_geo_zone();
		$geo_zone->data = functions::array_update($geo_zone->data, $data);
		$geo_zone->save();

		// Check if the entity was created
		if (!$geo_zone_id = $geo_zone->data['id']) {
			throw new Exception('Failed to create geo zone');
		}

		########################################################################
		## Load and check the geo zone
		########################################################################

		// Load the entity
		$geo_zone = new ent_geo_zone($geo_zone_id);

		// Check if the geo zone was loaded
		if ($geo_zone->data['id'] != $geo_zone_id) {
			throw new Exception('Failed to load geo zone');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $geo_zone->data)) {
			throw new Exception('The geo zone data was not stored correctly');
		}

		########################################################################
		## Updating the geo zone
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Updated Geo Zone',
			'description' => 'This is an updated geo zone',
			'priority' => 2,
		];

		// Update some data
		$geo_zone->data = functions::array_update($geo_zone->data, $data);

		// Save changes to database
		$geo_zone->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $geo_zone->data)) {
			throw new Exception('The geo zone data was not updated correctly');
		}

		########################################################################
		## Deleting the geo zone
		########################################################################

		// Delete the entity
		$geo_zone->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."geo_zones
			where id = ". (int)$geo_zone_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete geo zone');
		}

		echo '  Test passed successfully!' . PHP_EOL;
		return true;

	} catch (Exception $e) {

		echo 'Test failed: '. $e->getMessage();
		return false;

	} finally {

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."geo_zones
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);

		// Rollback changes to the database
		database::query("rollback;");
	}
