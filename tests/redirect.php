<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."redirects';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'code' => '301',
			'from_url' => '/old-page',
			'to_url' => '/new-page',
		];

		########################################################################
		## Creating a new redirect
		########################################################################

		// Create a new entity
		$redirect = new ent_redirect();
		$redirect->data = functions::array_update($redirect->data, $data);
		$redirect->save();

		// Check if the entity was created
		if (!$redirect_id = $redirect->data['id']) {
			throw new Exception('Failed to create redirect');
		}

		########################################################################
		## Load and check the redirect
		########################################################################

		// Load the entity
		$redirect = new ent_redirect($redirect_id);

		// Check if the redirect was loaded
		if ($redirect->data['id'] != $redirect_id) {
			throw new Exception('Failed to load redirect');
		}

		// Check if data was set correctly
		foreach ($data as $key => $value) {
			if ($redirect->data[$key] != $value) {
				throw new Exception('The redirect data was not stored correctly ('. $key .')');
			}
		}

		########################################################################
		## Updating the redirect
		########################################################################

		// Prepare some new data
		$data = [
			'code' => '302',
			'from_url' => '/old-page-updated',
			'to_url' => '/new-page-updated',
		];

		// Update some data
		$redirect->data = functions::array_update($redirect->data, $data);

		// Save changes to database
		$redirect->save();

		// Check if data was set correctly
		foreach ($data as $key => $value) {
			if ($redirect->data[$key] != $value) {
				throw new Exception('The redirect data was not updated correctly ('. $key .')');
			}
		}

		########################################################################
		## Deleting the redirect
		########################################################################

		// Delete the entity
		$redirect->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."redirects
			where id = ". (int)$redirect_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete redirect');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."redirects
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
