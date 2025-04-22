<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Start a MySQL transaction so we can rollback the test
		database::query("start transaction;");

		// Fetch the current auto increment ID
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."languages';"
		)->fetch('Auto_increment');

		// Prepare some example data
		$data = [
			'name' => 'Spanish',
			'code' => 'es',
			'direction' => 'ltr',
			'locale' => 'es_ES',
			'url_type' => 'none',
			'domain_name' => 'example.es',
			'raw_date' => 'Y-m-d',
			'raw_time' => 'H:i:s',
			'raw_datetime' => 'Y-m-d H:i:s',
			'format_date' => 'd M Y',
			'format_time' => 'H:i',
			'format_datetime' => 'd M Y H:i',
			'decimal_point' => ',',
			'thousands_sep' => '.',
			'priority' => 1,
		];

		########################################################################
		## Creating a new language
		########################################################################

		// Create a new entity
		$language = new ent_language();
		$language->data = functions::array_update($language->data, $data);
		$language->save();

		// Check if the entity was created
		if (!$language_id = $language->data['id']) {
			throw new Exception('Failed to create language');
		}

		########################################################################
		## Load and check the language
		########################################################################

		// Load the entity
		$language = new ent_language($language_id);

		// Check if the language was loaded
		if ($language->data['id'] != $language_id) {
			throw new Exception('Failed to load language');
		}

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $language->data)) {
			throw new Exception('The language data was not stored correctly');
		}

		########################################################################
		## Updating the language
		########################################################################

		// Prepare some new data
		$data = [
			'name' => 'French',
			'code' => 'fr',
			'direction' => 'rtl',
			'locale' => 'fr_FR',
			'url_type' => 'path',
			'domain_name' => 'example.fr',
			'raw_date' => 'd/m/Y',
			'raw_time' => 'H:i',
			'raw_datetime' => 'd/m/Y H:i',
			'format_date' => 'd F Y',
			'format_time' => 'H:i:s',
			'format_datetime' => 'd F Y H:i:s',
			'decimal_point' => ',',
			'thousands_sep' => ' ',
			'priority' => 2,
		];

		// Update some data
		$language->data = functions::array_update($language->data, $data);

		// Save changes to database
		$language->save();

		// Check if data was set correctly
		if (!functions::array_intersect_compare($data, $language->data)) {
			throw new Exception('The language data was not updated correctly');
		}

		########################################################################
		## Deleting the language
		########################################################################

		// Delete the entity
		$language->delete();

		// Check if the entity was deleted
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."languages
			where id = ". (int)$language_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete language');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."languages
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}

