<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."attribute_groups';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction - so we can rollback the changes
		database::query("start transaction;");

		// Define some example data
		$data = [
			'code' => 'test_attribute_group',
			'sort' => 'alphabetical',
			'name' => [
				'en' => 'Test Attribute Group',
				//'fr' => 'Groupe d\'attributs de test',
			],
		];

		########################################################################
		## Creating a new attribute group
		########################################################################

		$attribute_group = new ent_attribute_group();
		$attribute_group->data = functions::array_update($attribute_group->data, $data);
		$attribute_group->save();

		if (!$attribute_group_id = $attribute_group->data['id']) {
			throw new Exception('Failed to create attribute group');
		}

		########################################################################
		## Load and check the attribute group
		########################################################################

		$attribute_group = new ent_attribute_group($attribute_group_id);

		if ($attribute_group->data['id'] != $attribute_group_id) {
			throw new Exception('Failed to load attribute group');
		}

		if (!functions::array_intersect_compare($data, $attribute_group->data)) {
			throw new Exception('The attribute group data was not stored correctly');
		}
		// Define some example data
		$data = [
			'code' => 'test_attribute_group_2',
			'sort' => 'priority',
			'name' => [
				'en' => 'Test Attribute Group 2',
				//'fr' => 'Groupe d\'attributs de test 2',
			],
		];

		$attribute_group->data = functions::array_update($attribute_group->data, $data);

		// Save changes
		$attribute_group->save();

		// Reload the attribute group
		$attribute_group = new ent_attribute_group($attribute_group_id);

		if (!functions::array_intersect_compare($data, $attribute_group->data)) {
			throw new Exception('The attribute group data was not updated correctly');
		}

		########################################################################
		## Delete the attribute group
		########################################################################

		$attribute_group->delete();

		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."attribute_groups
			where id = ". (int)$attribute_group_id ."
			limit 1;"
		)->num_rows()) {
			throw new Exception('Failed to delete attribute group');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."attribute_groups AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
