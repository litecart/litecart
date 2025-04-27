<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."pages';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'title' => ['en' => 'Test Page'],
			'content' => ['en' => 'This is a test page.'],
			'priority' => 1,
		];

		########################################################################
		## Creating a new page
		########################################################################

		// Create a new entity
		$page = new ent_page();
		$page->data = functions::array_update($page->data, $data);
		$page->save();

		// Check if the entity was created
		if (!$page_id = $page->data['id']) {
			throw new Exception('Failed to create page');
		}

		########################################################################
		## Load and check the page
		########################################################################

		// Load the entity
		$page = new ent_page($page_id);

		// Check if the page was loaded
		if ($page->data['id'] != $page_id) {
			throw new Exception('Failed to load page');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $page->data)) {
			throw new Exception('The page data was not stored correctly');
		}

		########################################################################
		## Updating the page
		########################################################################

		// Prepare some new data
		$data = [
			'title' => ['en' => 'Updated Test Page'],
			'content' => ['en' => 'This is an updated test page.'],
			'priority' => 2,
		];

		// Update some data
		$page->data = functions::array_update($page->data, $data);

		// Save changes to database
		$page->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $page->data)) {
			throw new Exception('The page data was not updated correctly ('. $key .')');
		}

		########################################################################
		## Deleting the page
		########################################################################

		// Delete the entity
		$page->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."pages
			where id = ". (int)$page_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete page');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."pages
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
