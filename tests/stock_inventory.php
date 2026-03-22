<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get auto increment IDs for rollback
		$stock_items_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."stock_items';"
		)->fetch('Auto_increment');

		$products_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."products';"
		)->fetch('Auto_increment');

		$stock_transactions_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."stock_transactions';"
		)->fetch('Auto_increment');

		database::query("start transaction;");

		########################################################################
		## AC-A1: Product with stock options references stock items
		########################################################################

		// Create a stock item
		$stock_item = new ent_stock_item();
		$stock_item->data['name'] = ['en' => 'Red T-Shirt L'];
		$stock_item->data['sku'] = 'TEST-STOCK-' . uniqid();
		$stock_item->data['quantity'] = 50;
		$stock_item->save();

		$stock_item_id = $stock_item->data['id'];

		if (!$stock_item_id) {
			throw new Exception('AC-A1: Failed to create stock item');
		}

		// Create a product with a stock option pointing to the stock item
		$product = new ent_product();
		$product->data['code'] = 'TEST-STOCK-OPT-' . uniqid();
		$product->data['name'] = ['en' => 'Test T-Shirt'];
		$product->data['status'] = 1;
		$product->data['stock_option_type'] = 'variant';
		$product->data['stock_options'] = [
			[
				'stock_item_id' => $stock_item_id,
				'priority' => 1,
				'price_modifier' => '',
				'price_adjustment' => [],
			],
		];
		$product->save();

		$product_id = $product->data['id'];

		if (!$product_id) {
			throw new Exception('AC-A1: Failed to create product');
		}

		// Reload product and verify stock options are loaded
		$product = new ent_product($product_id);

		if (empty($product->data['stock_options'])) {
			throw new Exception('AC-A1: Stock options not loaded on product');
		}

		$option = $product->data['stock_options'][0];

		if ((int)$option['stock_item_id'] !== $stock_item_id) {
			throw new Exception('AC-A1: Stock option does not reference the correct stock item. Expected '. $stock_item_id .', got '. $option['stock_item_id']);
		}

		########################################################################
		## AC-A2: Stock transactions adjust quantity correctly
		########################################################################

		// Create a stock item with quantity 0
		$stock_item_2 = new ent_stock_item();
		$stock_item_2->data['name'] = ['en' => 'Transaction Test Item'];
		$stock_item_2->data['sku'] = 'TEST-TXN-' . uniqid();
		$stock_item_2->data['quantity'] = 0;
		$stock_item_2->save();

		$stock_item_2_id = $stock_item_2->data['id'];

		// Create a deposit transaction (+25)
		$txn = new ent_stock_transaction();
		$txn->data['name'] = 'Test Deposit';
		$txn->data['contents'] = [
			[
				'stock_item_id' => $stock_item_2_id,
				'quantity_adjustment' => 25,
			],
		];
		$txn->save();

		// Reload stock item and verify quantity
		$stock_item_2 = new ent_stock_item($stock_item_2_id);

		if ((float)$stock_item_2->data['quantity'] != 25) {
			throw new Exception('AC-A2: After deposit of 25, quantity should be 25. Got '. $stock_item_2->data['quantity']);
		}

		// Create a withdrawal transaction (-10)
		$txn2 = new ent_stock_transaction();
		$txn2->data['name'] = 'Test Withdrawal';
		$txn2->data['contents'] = [
			[
				'stock_item_id' => $stock_item_2_id,
				'quantity_adjustment' => -10,
			],
		];
		$txn2->save();

		// Reload and verify final quantity = 25 - 10 = 15
		$stock_item_2 = new ent_stock_item($stock_item_2_id);

		if ((float)$stock_item_2->data['quantity'] != 15) {
			throw new Exception('AC-A2: After deposit 25 and withdrawal 10, quantity should be 15. Got '. $stock_item_2->data['quantity']);
		}

		########################################################################
		## AC-A3: Out of stock detection
		########################################################################

		// Create a stock item with quantity 0
		$oos_stock_item = new ent_stock_item();
		$oos_stock_item->data['name'] = ['en' => 'Out of Stock Item'];
		$oos_stock_item->data['sku'] = 'TEST-OOS-' . uniqid();
		$oos_stock_item->data['quantity'] = 0;
		$oos_stock_item->save();

		$oos_stock_item_id = $oos_stock_item->data['id'];

		// Create a product with stock option pointing to the OOS item
		$oos_product = new ent_product();
		$oos_product->data['code'] = 'TEST-OOS-' . uniqid();
		$oos_product->data['name'] = ['en' => 'OOS Product'];
		$oos_product->data['status'] = 1;
		$oos_product->data['stock_option_type'] = 'variant';
		$oos_product->data['stock_options'] = [
			[
				'stock_item_id' => $oos_stock_item_id,
				'priority' => 1,
				'price_modifier' => '',
				'price_adjustment' => [],
			],
		];
		$oos_product->save();

		// Reload and check availability
		$oos_product = new ent_product($oos_product->data['id']);
		$oos_option = $oos_product->data['stock_options'][0];

		if ((float)$oos_option['quantity_available'] != 0) {
			throw new Exception('AC-A3: quantity_available should be 0 for out-of-stock item. Got '. $oos_option['quantity_available']);
		}

		########################################################################
		## AC-A4: Cascade deletion of stock options
		########################################################################

		// Using the product from AC-A1, remove all stock options
		$product = new ent_product($product_id);
		$product->data['stock_options'] = [];
		$product->save();

		// Verify stock options are removed from DB
		$remaining = database::query(
			"select id from ". DB_TABLE_PREFIX ."products_stock_options
			where product_id = ". (int)$product_id ."
			limit 1;"
		)->num_rows;

		if ($remaining) {
			throw new Exception('AC-A4: Stock options should be removed when cleared from product');
		}

		########################################################################
		## AC-A5: Simple product without stock options
		########################################################################

		$simple_product = new ent_product();
		$simple_product->data['code'] = 'TEST-SIMPLE-' . uniqid();
		$simple_product->data['name'] = ['en' => 'Simple Product'];
		$simple_product->data['status'] = 1;
		$simple_product->save();

		$simple_product = new ent_product($simple_product->data['id']);

		if (!empty($simple_product->data['stock_options'])) {
			throw new Exception('AC-A5: Simple product should have no stock options');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::query('rollback;');

		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."stock_items AUTO_INCREMENT = ". (int)$stock_items_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."products AUTO_INCREMENT = ". (int)$products_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."stock_transactions AUTO_INCREMENT = ". (int)$stock_transactions_auto_id .";");
	}
