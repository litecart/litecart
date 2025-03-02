<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."categories';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction - so we can rollback the changes
		database::query("start transaction;");

		// Define some example data
		$data = [
			'status' => 1,
			'name' => [
				'en' => 'Test Category',
				'fr' => 'Catégorie de test',
			],
			'description' => [
				'en' => 'This is a test category',
				//'fr' => 'Ceci est une catégorie de test',
			],
			'parent_id' => 0,
			'priority' => 0,
		];

		########################################################################
		## Creating a new category
		########################################################################

		$category = new ent_category();
		$category->data = functions::array_update($category->data, $data);
		$category->save();

		if (!$category_id = $category->data['id']) {
			throw new Exception('Failed to create category');
		}

		########################################################################
		## Load and check the category
		########################################################################

		$category = new ent_category($category_id);

		if (!functions::array_intersect_compare($data, $category->data)) {
			throw new Exception('The category data was not stored correctly');
		}

		########################################################################
		## Update the category
		########################################################################

		// Define some example data
		$data = [
			'status' => 0,
			'name' => [
				'en' => 'Test Category 2',
				'fr' => 'Catégorie de test 2',
			],
			'description' => [
				'en' => 'This is a test category 2',
				'fr' => 'Ceci est une catégorie de test 2',
			],
			'keywords' => 'test,category2',
			'parent_id' => 1,
			'priority' => 1,
		];

		$category->data = functions::array_update($category->data, $data);
		$category->save();

		try {
			if (!functions::array_intersect_compare($data, $category->data)) {
				throw new Exception('The category data was not updated correctly');
			}
		} catch (Exception $e) {
			throw new Exception('Error in functions::array_intersect_compare function: ' . $e->getMessage());
		}

		########################################################################
		## Delete the category
		########################################################################

		$category->delete();

		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."categories
			where id = ". (int)$category_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete category');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."categories AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}

