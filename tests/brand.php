<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."brands';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction - so we can rollback the changes
		database::query("start transaction;");

		// Define some example data
		$data = [
			'status' => 1,
			'name' => 'Test Brand',
			'description' => [
				'en' => 'This is a test brand'
				//'fr' => 'Ceci est une marque de test'
			],
			'head_title' => [
				'en' => 'Test Brand'
				//'fr' => ''
			],
			'meta_description' => [
				'en' => 'This is a test brand'
				//'fr' => ''
			],
			'link' => [
				'en' => 'https://www.example.com'
				//'fr' => ''
			],
			'keywords' => 'test,brand',
			'image' => '',
		];

		########################################################################
		## Creating a new brand
		########################################################################

		$brand = new ent_brand();
		$brand->data = functions::array_update($brand->data, $data);
		$brand->save();

		if (!$brand_id = $brand->data['id']) {
			throw new Exception('Failed to create brand');
		}

		########################################################################
		## Load and check the brand
		########################################################################

		$brand = new ent_brand($brand_id);

		if ($brand->data['id'] != $brand_id) {
			throw new Exception('Failed to load brand');
		}

		if (!functions::array_intersect_compare($data, $brand->data)) {
			throw new Exception('The brand data was not stored correctly');
		}
		// Define some example data
		$data = [
			'status' => 0,
			'name' => 'Test Brand 2',
			'description' => [
				'en' => 'This is a test brand 2',
				//'fr' => 'Ceci est une marque de test 2'
			],
			'head_title' => [
				'en' => 'Test Brand 2'
				//'fr' => ''
			],
			'meta_description' => [
				'en' => 'This is a test brand 2'
				//'fr' => ''
			],
			'link' => [
				'en' => 'https://www.example.com'
				//'fr' => ''
			],
			'keywords' => 'test,brand2',
			'image' => '',
		];

		$brand->data = functions::array_update($brand->data, $data);

		$brand->save();

		if (!functions::array_intersect_compare($data, $brand->data)) {
			throw new Exception('The brand data was not updated correctly');
		}

		########################################################################
		## Delete the brand
		########################################################################

		$brand->delete();

		if (database::query(
			"select * from ". DB_TABLE_PREFIX ."brands
			where id = ". (int)$brand_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete brand');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."brands AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
