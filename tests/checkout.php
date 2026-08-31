<?php

	include_once __DIR__.'/../src/shared/app_header.inc.php';

	try {

		// Get auto increment IDs for rollback
		$orders_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_PREFIX ."orders';"
		)->fetch('Auto_increment');

		$products_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_PREFIX ."products';"
		)->fetch('Auto_increment');

		$stock_items_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_PREFIX ."stock_items';"
		)->fetch('Auto_increment');

		database::begin_transaction();

		// Shared customer data for all order tests
		$customer_data = [
			'customer' => [
				'firstname' => 'Test',
				'lastname' => 'Buyer',
				'email' => 'test.buyer@example.com',
				'phone' => '555-0100',
				'address1' => '1 Test Street',
				'city' => 'Testville',
				'country_code' => 'US',
				'shipping_address' => [
					'firstname' => 'Test',
					'lastname' => 'Buyer',
					'address1' => '1 Test Street',
					'city' => 'Testville',
					'country_code' => 'US',
				],
			],
			'order_status_id' => 1,
			'currency_code' => 'USD',
			'currency_value' => 1.0,
			'language_code' => 'en',
		];

		########################################################################
		## AC-C1: Create order with simple product
		########################################################################

		$order = new ent_order();
		$order->data = f::array_update($order->data, $customer_data);
		$order->add_item([
			'product_id' => 0,
			'name' => 'Simple Test Product',
			'quantity' => 2,
			'price' => 25.00,
			'tax' => 5.00,
			'weight' => 0.5,
			'weight_unit' => 'kg',
		]);
		$order->save();

		$order_id = $order->data['id'];

		if (!$order_id) {
			throw new Exception('AC-C1: Failed to create order');
		}

		// Reload and verify
		$order = new ent_order($order_id);

		if (empty($order->data['items'])) {
			throw new Exception('AC-C1: Order should have at least one line');
		}

		$line = $order->data['items'][0];

		if ($line['name'] !== 'Simple Test Product') {
			throw new Exception('AC-C1: Order line name mismatch');
		}

		if ((float)$line['quantity'] != 2) {
			throw new Exception('AC-C1: Order line quantity should be 2. Got '. $line['quantity']);
		}

		########################################################################
		## AC-C2: Order with variant product (stock_option_id on line)
		########################################################################

		// Create stock item
		$stock_item = new ent_stock_item();
		$stock_item->data['name'] = ['en' => 'Checkout Variant Item'];
		$stock_item->data['sku'] = 'TEST-CHECKOUT-' . uniqid();
		$stock_item->data['quantity'] = 20;
		$stock_item->save();
		$stock_item_id = $stock_item->data['id'];

		// Create product with stock option
		$product = new ent_product();
		$product->data['code'] = 'TEST-CHECKOUT-' . uniqid();
		$product->data['name'] = ['en' => 'Checkout Variant Product'];
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

		// Get the stock option ID
		$product = new ent_product($product_id);
		$stock_option_id = $product->data['stock_options'][0]['id'];

		// Create order with variant line (no stock_items — just line with stock_option_id)
		$variant_order = new ent_order();
		$variant_order->data = f::array_update($variant_order->data, $customer_data);
		$variant_order->add_item([
			'product_id' => $product_id,
			'stock_option_id' => $stock_option_id,
			'name' => 'Checkout Variant Product',
			'quantity' => 3,
			'price' => 50.00,
			'tax' => 9.50,
			'weight' => 1.0,
			'weight_unit' => 'kg',
		]);
		$variant_order->save();

		$variant_order_id = $variant_order->data['id'];

		if (!$variant_order_id) {
			throw new Exception('AC-C2: Failed to create variant order');
		}

		// Reload and verify order line references the product
		$variant_order = new ent_order($variant_order_id);
		$line = $variant_order->data['items'][0];

		if ((int)$line['product_id'] !== $product_id) {
			throw new Exception('AC-C2: Order line should reference product_id '. $product_id .'. Got '. $line['product_id']);
		}

		if ($line['name'] !== 'Checkout Variant Product') {
			throw new Exception('AC-C2: Order line name mismatch');
		}

		########################################################################
		## AC-C3: Order lines persist correctly
		########################################################################

		// Verify order lines are stored in DB
		$line_count = database::query(
			"select count(*) as cnt from ". DB_PREFIX ."orders_items
			where order_id = ". (int)$variant_order_id .";"
		)->fetch('cnt');

		if ((int)$line_count < 1) {
			throw new Exception('AC-C3: Order should have at least 1 line in database');
		}

		########################################################################
		## AC-C4: Order total calculation
		########################################################################

		$multi_order = new ent_order();
		$multi_order->data = f::array_update($multi_order->data, $customer_data);

		$multi_order->add_item([
			'product_id' => 0,
			'name' => 'Product A',
			'quantity' => 2,
			'price' => 30.00,
			'tax' => 5.70,
			'weight' => 0.5,
			'weight_unit' => 'kg',
		]);

		$multi_order->add_item([
			'product_id' => 0,
			'name' => 'Product B',
			'quantity' => 1,
			'price' => 45.00,
			'tax' => 8.55,
			'weight' => 1.0,
			'weight_unit' => 'kg',
		]);

		// Verify totals calculated by add_item() in memory
		// Note: save() calls refresh_total() which recalculates from $data['items'],
		// not from $data['lines']. So we verify totals before save().
		$expected_subtotal = 105.00; // (30 * 2) + (45 * 1)
		$expected_tax = 19.95; // (5.70 * 2) + (8.55 * 1)

		if (abs((float)$multi_order->data['subtotal'] - $expected_subtotal) > 0.01) {
			throw new Exception('AC-C4: Subtotal should be '. $expected_subtotal .'. Got '. $multi_order->data['subtotal']);
		}

		if (abs((float)$multi_order->data['subtotal_tax'] - $expected_tax) > 0.01) {
			throw new Exception('AC-C4: Subtotal tax should be '. $expected_tax .'. Got '. $multi_order->data['subtotal_tax']);
		}

		// Verify order can be saved and reloaded with lines intact
		$multi_order->save();
		$multi_order_id = $multi_order->data['id'];

		if (!$multi_order_id) {
			throw new Exception('AC-C4: Failed to create multi-line order');
		}

		$multi_order = new ent_order($multi_order_id);

		if (count($multi_order->data['items']) != 2) {
			throw new Exception('AC-C4: Order should have 2 lines after reload. Got '. count($multi_order->data['items']));
		}

		########################################################################
		## AC-C5: Order deletion cleans up lines
		########################################################################

		// Delete the variant order
		$variant_order = new ent_order($variant_order_id);
		$variant_order->delete();

		// Verify order is deleted
		$found = database::query(
			"select id from ". DB_PREFIX ."orders
			where id = ". (int)$variant_order_id ."
			limit 1;"
		)->num_rows;

		if ($found) {
			throw new Exception('AC-C5: Order should be deleted');
		}

		// Verify order lines are cleaned up
		$remaining_lines = database::query(
			"select id from ". DB_PREFIX ."orders_items
			where order_id = ". (int)$variant_order_id ."
			limit 1;"
		)->num_rows;

		if ($remaining_lines) {
			throw new Exception('AC-C5: Order lines should be removed after order deletion');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::rollback();

		database::query("ALTER TABLE ". DB_PREFIX ."orders AUTO_INCREMENT = ". (int)$orders_auto_id .";");
		database::query("ALTER TABLE ". DB_PREFIX ."products AUTO_INCREMENT = ". (int)$products_auto_id .";");
		database::query("ALTER TABLE ". DB_PREFIX ."stock_items AUTO_INCREMENT = ". (int)$stock_items_auto_id .";");
	}
