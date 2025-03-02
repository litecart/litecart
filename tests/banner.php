<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."banners';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction - so we can rollback the changes
		database::query("start transaction;");

		// Define some example data
		$data = [
			'status' => 1,
			'name' => 'Test Banner',
			'html' => '<p>This is a test banner</p>',
			'keywords' => 'test,banner',
			'languages' => ['en'],
			'date_valid_from' => '2023-01-01 00:00:00',
			'date_valid_to' => '2023-12-31 23:59:59',
		];

		########################################################################
		## Creating a new banner
		########################################################################

		$banner = new ent_banner();
		$banner->data = functions::array_update($banner->data, $data);
		$banner->save();

		if (!$banner_id = $banner->data['id']) {
			throw new Exception('Failed to create banner');
		}

		########################################################################
		## Load and check the banner
		########################################################################

		$banner = new ent_banner($banner_id);

		if ($banner->data['id'] != $banner_id) {
			throw new Exception('Failed to load banner');
		}

		if (!functions::array_intersect_compare($data, $banner->data)) {
			throw new Exception('The banner data was not stored correctly');
		}

		// Define some example data
		$data = [
			'status' => 0,
			'name' => 'Test Banner 2',
			'html' => '<p>This is a test banner 2</p>',
			'keywords' => 'test,banner2',
			'languages' => ['en'],
			'date_valid_from' => '2024-01-01 00:00:00',
			'date_valid_to' => '2024-12-31 23:59:59',
		];

		$banner->data = functions::array_update($banner->data, $data);
		$banner->save();

		if (!functions::array_intersect_compare($data, $banner->data)) {
			throw new Exception('The banner data was not updated correctly');
		}

		########################################################################
		## Delete the banner
		########################################################################

		$banner->delete();

		// Check if the banner was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."banners
			where id = ". (int)$banner_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete banner');
		}

		echo '  Test passed successfully!' . PHP_EOL;
		return true;

	} catch (Exception $e) {

		echo '  Error: ' . $e->getMessage() . PHP_EOL;
		return false;

	} finally {

		// Rollback changes to the database
		database::query('rollback;');

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."banners AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
