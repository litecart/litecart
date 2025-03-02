<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."countries';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction - so we can rollback the changes
		database::query("start transaction;");

		// Define some example data
		$data = [
			'status' => 1,
			'iso_code_1' => '999',
			'iso_code_2' => 'XX',
			'iso_code_3' => 'XXX',
			'name' => 'Test Country',
			'domestic_name' => 'Test Country Domestic',
			'tax_id_format' => '',
			'address_format' => '',
			'postcode_format' => '',
			'language_code' => 'en',
			'currency_code' => 'USD',
			'phone_code' => '123',
		];

		########################################################################
		## Creating a new country
		########################################################################

		$country = new ent_country();
		$country->data = functions::array_update($country->data, $data);
		$country->save();

		if (!$country_id = $country->data['id']) {
			throw new Exception('Failed to create country');
		}

		########################################################################
		## Load and check the country
		########################################################################

		$country = new ent_country($country_id);

		if (!functions::array_intersect_compare($data, $country->data)) {
			throw new Exception('The country data was not stored correctly');
		}

		########################################################################
		## Update the country
		########################################################################

		// Define some example data
		$data = [
			'status' => 0,
			'iso_code_2' => '888',
			'iso_code_2' => 'YY',
			'iso_code_3' => 'YYY',
			'name' => 'Test Country 2',
			'domestic_name' => 'Test Country Domestic 2',
			'tax_id_format' => '',
			'address_format' => '',
			'postcode_format' => '',
			'language_code' => 'fr',
			'currency_code' => 'EUR',
			'phone_code' => '456',
		];

		$country->data = functions::array_update($country->data, $data);

		$country->save();

		if (!functions::array_intersect_compare($data, $country->data)) {
			throw new Exception('The country data was not updated correctly');
		}

		########################################################################
		## Delete the country
		########################################################################

		$country->delete();

		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."countries
			where id = ". (int)$country_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete country');
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
			"ALTER TABLE ". DB_TABLE_PREFIX ."countries AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}

