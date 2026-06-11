<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get auto increment IDs for rollback
		$products_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."products';"
		)->fetch('Auto_increment');

		$categories_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."categories';"
		)->fetch('Auto_increment');

		$tax_classes_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."tax_classes';"
		)->fetch('Auto_increment');

		$tax_rates_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."tax_rates';"
		)->fetch('Auto_increment');

		database::begin_transaction();

		########################################################################
		## AC-D1: Multilingual product fields (JSON columns)
		########################################################################

		$product = new ent_product();
		$product->data['code'] = 'TEST-CATALOG-' . uniqid();
		$product->data['name'] = ['en' => 'Test Shirt', 'de' => 'Test Hemd'];
		$product->data['short_description'] = ['en' => 'A test shirt', 'de' => 'Ein Test-Hemd'];
		$product->data['description'] = ['en' => 'Full description', 'de' => 'Volle Beschreibung'];
		$product->data['status'] = 1;
		$product->data['prices'] = [['price' => [currency::$selected['code'] => 29.99]]];
		$product->save();

		if (!$product_id = $product->data['id']) {
			throw new Exception('AC-D1: Failed to create product');
		}

		// Reload and verify multilingual fields
		$product = new ent_product($product_id);

		if ($product->data['name']['en'] !== 'Test Shirt') {
			throw new Exception('AC-D1: English name not stored correctly');
		}

		if ($product->data['name']['de'] !== 'Test Hemd') {
			throw new Exception('AC-D1: German name not stored correctly');
		}

		if ($product->data['short_description']['en'] !== 'A test shirt') {
			throw new Exception('AC-D1: English short_description not stored correctly');
		}

		if ($product->data['short_description']['de'] !== 'Ein Test-Hemd') {
			throw new Exception('AC-D1: German short_description not stored correctly');
		}

		########################################################################
		## AC-D2: Category filter
		########################################################################

		// Create a category
		$category = new ent_category();
		$category->data['name'] = ['en' => 'Test Category'];
		$category->data['status'] = 1;
		$category->save();

		if (!$category_id = $category->data['id']) {
			throw new Exception('AC-D2: Failed to create category');
		}

		// Link product to category
		$product = new ent_product($product_id);
		$product->data['categories'] = [$category_id];
		$product->save();

		// Verify product is linked to category
		$product = new ent_product($product_id);

		if (!in_array($category_id, $product->data['categories'])) {
			throw new Exception('AC-D2: Product not linked to category');
		}

		// Query products by category
		$found = database::query(
			"select p.id from ". DB_TABLE_PREFIX ."products p
			inner join ". DB_TABLE_PREFIX ."products_to_categories pc on (pc.product_id = p.id)
			where pc.category_id = ". (int)$category_id ."
			and p.id = ". (int)$product_id ."
			limit 1;"
		)->num_rows;

		if (!$found) {
			throw new Exception('AC-D2: Product not found when filtering by category');
		}

		########################################################################
		## AC-D3: Price with tax class
		########################################################################

		// Create tax class
		database::query(
			"insert into ". DB_TABLE_PREFIX ."tax_classes
			(name, description, created_at)
			values ('Test Tax 19%', 'German VAT', '". date('Y-m-d H:i:s') ."');"
		);
		$tax_class_id = database::insert_id();

		if (!$tax_class_id) {
			throw new Exception('AC-D3: Failed to create tax class');
		}

		// Assign tax class to product
		$product = new ent_product($product_id);
		$product->data['tax_class_id'] = $tax_class_id;
		$product->save();

		// Reload and verify tax class is persisted
		$product = new ent_product($product_id);

		if ((int)$product->data['tax_class_id'] !== $tax_class_id) {
			throw new Exception('AC-D3: Product tax_class_id not persisted. Expected '. $tax_class_id .', got '. $product->data['tax_class_id']);
		}

		// Verify tax class exists in DB
		$found = database::query(
			"select id from ". DB_TABLE_PREFIX ."tax_classes
			where id = ". (int)$tax_class_id ."
			limit 1;"
		)->num_rows;

		if (!$found) {
			throw new Exception('AC-D3: Tax class not found in database');
		}

		########################################################################
		## AC-D4: Inactive products
		########################################################################

		// Create an inactive product
		$inactive_product = new ent_product();
		$inactive_product->data['code'] = 'TEST-INACTIVE-' . uniqid();
		$inactive_product->data['name'] = ['en' => 'Inactive Product'];
		$inactive_product->data['status'] = 0;
		$inactive_product->save();

		$inactive_id = $inactive_product->data['id'];

		if (!$inactive_id) {
			throw new Exception('AC-D4: Failed to create inactive product');
		}

		// Query active products only
		$found = database::query(
			"select id from ". DB_TABLE_PREFIX ."products
			where id = ". (int)$inactive_id ."
			and status = 1
			limit 1;"
		)->num_rows;

		if ($found) {
			throw new Exception('AC-D4: Inactive product should not appear in active product query');
		}

		// Verify it exists when not filtering by status
		$found = database::query(
			"select id from ". DB_TABLE_PREFIX ."products
			where id = ". (int)$inactive_id ."
			limit 1;"
		)->num_rows;

		if (!$found) {
			throw new Exception('AC-D4: Inactive product should still exist in database');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::rollback();

		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."products AUTO_INCREMENT = ". (int)$products_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."categories AUTO_INCREMENT = ". (int)$categories_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."tax_classes AUTO_INCREMENT = ". (int)$tax_classes_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."tax_rates AUTO_INCREMENT = ". (int)$tax_rates_auto_id .";");
	}
