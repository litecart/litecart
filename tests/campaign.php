<?php

	include_once __DIR__.'/../src/shared/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."campaigns';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction - so we can rollback the changes
		database::begin_transaction();

		// Define some example data
		$data = [
			'name' => 'Test Campaign',
			'valid_from' => date('Y-m-d H:i:s', strtotime('-1 day')),
			'valid_to' => date('Y-m-d H:i:s', strtotime('+30 days')),
		];

		########################################################################
		## Creating a new campaign
		########################################################################

		$campaign = new ent_campaign();
		$campaign->data = f::array_update($campaign->data, $data);
		$campaign->save();

		if (!$campaign_id = $campaign->data['id']) {
			throw new Exception('Failed to create campaign');
		}

		########################################################################
		## Load and check the campaign
		########################################################################

		$campaign = new ent_campaign($campaign_id);

		if ($campaign->data['id'] != $campaign_id) {
			throw new Exception('Failed to load campaign');
		}

		if ($campaign->data['name'] != $data['name']) {
			throw new Exception('Campaign name was not stored correctly');
		}

		########################################################################
		## Update the campaign
		########################################################################

		$update_data = [
			'name' => 'Updated Campaign',
			'valid_from' => date('Y-m-d H:i:s', strtotime('+1 day')),
			'valid_to' => date('Y-m-d H:i:s', strtotime('+60 days')),
		];

		$campaign->data = f::array_update($campaign->data, $update_data);
		$campaign->save();

		$campaign = new ent_campaign($campaign_id);

		if ($campaign->data['name'] != $update_data['name']) {
			throw new Exception('Campaign name was not updated correctly');
		}

		########################################################################
		## Test campaign with product prices
		########################################################################

		// Find a product to associate with the campaign
		$product = database::query(
			"select id from ". DB_TABLE_PREFIX ."products
			limit 1;"
		)->fetch();

		if ($product) {

			$campaign->data['products'] = [
				[
					'id' => '',
					'product_id' => $product['id'],
					'customer_group_id' => '',
					'geo_zone_id' => '',
					'price' => [settings::get('store_currency_code') => '9.99'],
				],
			];

			$campaign->save();

			// Verify the campaign price was stored
			$price = database::query(
				"select * from ". DB_TABLE_PREFIX ."products_prices
				where campaign_id = ". (int)$campaign_id ."
				limit 1;"
			)->fetch();

			if (!$price) {
				throw new Exception('Failed to store campaign product price');
			}

			// Update price
			$campaign->data['products'][0]['price'] = [settings::get('store_currency_code') => '14.99'];
			$campaign->save();

			$price = database::query(
				"select price from ". DB_TABLE_PREFIX ."products_prices
				where campaign_id = ". (int)$campaign_id ."
				limit 1;"
			)->fetch('price');

			$price_data = json_decode($price, true);

			if (empty($price_data[settings::get('store_currency_code')]) || $price_data[settings::get('store_currency_code')] != '14.99') {
				throw new Exception('Failed to update campaign product price');
			}

			// Remove product from campaign
			$campaign->data['products'] = [];
			$campaign->save();

			$remaining = database::query(
				"select count(*) as cnt from ". DB_TABLE_PREFIX ."products_prices
				where campaign_id = ". (int)$campaign_id .";"
			)->fetch('cnt');

			if ($remaining != 0) {
				throw new Exception('Failed to remove campaign product prices');
			}
		}

		########################################################################
		## Delete the campaign
		########################################################################

		$campaign->delete();

		if (database::query(
			"select * from ". DB_TABLE_PREFIX ."campaigns
			where id = ". (int)$campaign_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete campaign');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		// Rollback changes to the database
		database::rollback();

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."campaigns AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
