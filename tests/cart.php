<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get auto increment IDs for rollback
		$products_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."products';"
		)->fetch('Auto_increment');

		$stock_items_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."stock_items';"
		)->fetch('Auto_increment');

		$cart_items_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."cart_items';"
		)->fetch('Auto_increment');

		database::begin_transaction();

		// Create a simple product for cart tests
		$product = new ent_product();
		$product->data['code'] = 'TEST-CART-' . uniqid();
		$product->data['name'] = ['en' => 'Cart Test Product'];
		$product->data['status'] = 1;
		$product->data['prices'] = [['price' => [currency::$selected['code'] => 49.99]]];
		$product->save();
		$product_id = $product->data['id'];

		if (!$product_id) {
			throw new Exception('Setup: Failed to create test product');
		}

		// Create a stock item + product with variant for AC-B2/B5
		$stock_item = new ent_stock_item();
		$stock_item->data['name'] = ['en' => 'Blue Variant'];
		$stock_item->data['sku'] = 'TEST-CART-VAR-' . uniqid();
		$stock_item->data['quantity'] = 2;
		$stock_item->save();
		$stock_item_id = $stock_item->data['id'];

		$variant_product = new ent_product();
		$variant_product->data['code'] = 'TEST-CART-VAR-' . uniqid();
		$variant_product->data['name'] = ['en' => 'Variant Product'];
		$variant_product->data['status'] = 1;
		$variant_product->data['stock_option_type'] = 'variant';
		$variant_product->data['prices'] = [['price' => [currency::$selected['code'] => 79.99]]];
		$variant_product->data['stock_options'] = [
			[
				'stock_item_id' => $stock_item_id,
				'priority' => 1,
				'price_modifier' => '',
				'price_adjustment' => [],
			],
		];
		$variant_product->save();
		$variant_product_id = $variant_product->data['id'];

		// Get the stock option ID that was created
		$variant_product = new ent_product($variant_product_id);
		$stock_option_id = $variant_product->data['stock_options'][0]['id'];

		// Clear cart before tests
		cart::clear();

		########################################################################
		## AC-B1: Add simple product to cart
		########################################################################

		$result = cart::add_product($product_id, null, [], 1, true);

		if (empty(cart::$items)) {
			throw new Exception('AC-B1: Cart should contain one item after adding product');
		}

		$item_key = array_key_first(cart::$items);
		$item = cart::$items[$item_key];

		if ((int)$item['product_id'] !== $product_id) {
			throw new Exception('AC-B1: Cart item product_id mismatch');
		}

		if ((float)$item['quantity'] != 1) {
			throw new Exception('AC-B1: Cart item quantity should be 1. Got '. $item['quantity']);
		}

		if ((float)$item['regular_price']['value'] <= 0) {
			throw new Exception('AC-B1: Cart item price should be greater than 0. Got '. $item['regular_price']['value']);
		}

		if ((float)$item['final_price']['value'] <= 0) {
			throw new Exception('AC-B1: Cart item price should be greater than 0. Got '. $item['final_price']['value']);
		}

		// Clear cart for next test
		cart::clear();

		########################################################################
		## AC-B2: Add variant product to cart
		########################################################################

		cart::add_product($variant_product_id, $stock_option_id, [], 1, true);

		if (empty(cart::$items)) {
			throw new Exception('AC-B2: Cart should contain one item after adding variant product');
		}

		$item_key = array_key_first(cart::$items);
		$item = cart::$items[$item_key];

		if ((int)$item['stock_option_id'] !== (int)$stock_option_id) {
			throw new Exception('AC-B2: Cart item should reference stock_option_id '. $stock_option_id .'. Got '. $item['stock_option_id']);
		}

		// Clear cart for next test
		cart::clear();

		########################################################################
		## AC-B3: Update cart item quantity
		########################################################################

		cart::add_product($product_id, null, [], 1, true);
		$item_key = array_key_first(cart::$items);

		cart::update($item_key, 3, true);

		if ((float)cart::$items[$item_key]['quantity'] != 3) {
			throw new Exception('AC-B3: Cart item quantity should be 3 after update. Got '. cart::$items[$item_key]['quantity']);
		}

		// Clear cart for next test
		cart::clear();

		########################################################################
		## AC-B4: Remove item from cart
		########################################################################

		cart::add_product($product_id, null, [], 1, true);
		$item_key = array_key_first(cart::$items);

		cart::remove($item_key, true);

		if (!empty(cart::$items)) {
			throw new Exception('AC-B4: Cart should be empty after removing the only item');
		}

		// Clear cart for next test
		cart::clear();

		########################################################################
		## AC-B5: Stock limit enforcement
		########################################################################

		// The variant product has stock_item with quantity=2
		cart::add_product($variant_product_id, $stock_option_id, [], 5, true);

		if (!empty(cart::$items)) {
			$item_key = array_key_first(cart::$items);
			$item = cart::$items[$item_key];

			// Item should have an error or quantity should be limited
			if (empty($item['error']) && (float)$item['quantity'] > 2) {
				throw new Exception('AC-B5: Adding 5 units when only 2 available should produce an error or limit quantity');
			}
		}

		// Clear cart
		cart::clear();

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		cart::clear();

		database::rollback();

		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."products AUTO_INCREMENT = ". (int)$products_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."stock_items AUTO_INCREMENT = ". (int)$stock_items_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."cart_items AUTO_INCREMENT = ". (int)$cart_items_auto_id .";");
	}
