<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		 // Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."site_tags';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'name' => 'New Tag',
			'description' => 'This is a new site tag.',
		];

		########################################################################
		## Creating a new site tag
		########################################################################

		// Create a new entity
		$site_tag = new ent_site_tag();
		$site_tag->data = functions::array_update($site_tag->data, $data);
		$site_tag->save();

		// Check if the entity was created
		if (!$site_tag_id = $site_tag->data['id']) {
			throw new Exception('Failed to create site tag');
		}

		########################################################################
		## Load and check the site tag
		########################################################################

		// Load the entity
		$site_tag = new ent_site_tag($site_tag_id);

		// Check if the site tag was loaded
		if ($site_tag->data['id'] != $site_tag_id) {
			throw new Exception('Failed to load site tag');
		}

		// Check if data was set correctly
		foreach ($data as $key => $value) {
			if ($site_tag->data[$key] != $value) {
				throw new Exception('The site tag data was not stored correctly ('. $key .')');
			}
		}

		########################################################################
		## Updating the site tag
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'Updated Tag',
			'description' => 'This is an updated site tag.',
		];

		// Update some data
		$site_tag->data = functions::array_update($site_tag->data, $data);

		// Save changes to database
		$site_tag->save();

		// Check if data was set correctly
		foreach ($data as $key => $value) {
			if ($site_tag->data[$key] != $value) {
				throw new Exception('The site tag data was not updated correctly ('. $key .')');
			}
		}

		########################################################################
		## Deleting the site tag
		########################################################################

		// Delete the entity
		$site_tag->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."site_tags
			where id = ". (int)$site_tag_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete site tag');
		}

		echo '  Test passed successfully!' . PHP_EOL;
		return true;

	} catch (Exception $e) {
		echo 'Test failed: '. $e->getMessage();

	} finally {

		// Rollback changes to the database
		database::query("rollback;");

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."site_tags
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
