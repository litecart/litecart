<?php

	include_once __DIR__.'/../public_html/shared/app_header.inc.php';

	try {

		// Save auto increment IDs
		$products_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."products';"
		)->fetch('Auto_increment');

		$categories_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."categories';"
		)->fetch('Auto_increment');

		$brands_auto_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."brands';"
		)->fetch('Auto_increment');

		database::begin_transaction();

		// Create test category
		$category = new ent_category();
		$category->data['name'] = ['en' => 'Catalog Test Category'];
		$category->data['status'] = 1;
		$category->save();
		$category_id = $category->data['id'];

		// Create test brand
		$brand = new ent_brand();
		$brand->data['name'] = 'Catalog Test Brand';
		$brand->data['status'] = 1;
		$brand->save();
		$brand_id = $brand->data['id'];

		// Create test products
		$product1 = new ent_product();
		$product1->data['code'] = 'CAT-TEST-1-'. uniqid();
		$product1->data['name'] = ['en' => 'Alpha Widget'];
		$product1->data['status'] = 1;
		$product1->data['brand_id'] = $brand_id;
		$product1->data['categories'] = [$category_id];
		$product1->data['prices'] = [['price' => [currency::$selected['code'] => 29.99]]];
		$product1->save();
		$product1_id = $product1->data['id'];

		$product2 = new ent_product();
		$product2->data['code'] = 'CAT-TEST-2-'. uniqid();
		$product2->data['name'] = ['en' => 'Beta Gadget'];
		$product2->data['status'] = 1;
		$product2->data['brand_id'] = $brand_id;
		$product2->data['categories'] = [$category_id];
		$product2->data['prices'] = [['price' => [currency::$selected['code'] => 49.99]]];
		$product2->save();
		$product2_id = $product2->data['id'];

		$product_inactive = new ent_product();
		$product_inactive->data['code'] = 'CAT-TEST-OFF-'. uniqid();
		$product_inactive->data['name'] = ['en' => 'Inactive Product'];
		$product_inactive->data['status'] = 0;
		$product_inactive->data['categories'] = [$category_id];
		$product_inactive->data['prices'] = [['price' => [currency::$selected['code'] => 9.99]]];
		$product_inactive->save();
		$product_inactive_id = $product_inactive->data['id'];

		########################################################################
		## catalog_products_query — category filter
		########################################################################

		$products = f::catalog_products_query([
			'categories' => [$category_id],
		])->fetch_all();

		$product_ids = array_column($products, 'id');

		if (!in_array($product1_id, $product_ids)) {
			throw new Exception('catalog_products_query: Active product 1 should appear in category listing');
		}

		if (!in_array($product2_id, $product_ids)) {
			throw new Exception('catalog_products_query: Active product 2 should appear in category listing');
		}

		########################################################################
		## catalog_products_query — inactive products excluded
		########################################################################

		if (in_array($product_inactive_id, $product_ids)) {
			throw new Exception('catalog_products_query: Inactive product should not appear in listing');
		}

		########################################################################
		## catalog_products_query — brand filter
		########################################################################

		$products = f::catalog_products_query([
			'brands' => [$brand_id],
		])->fetch_all();

		$product_ids = array_column($products, 'id');

		if (count($product_ids) < 2) {
			throw new Exception('catalog_products_query: Brand filter should return at least 2 products, got '. count($product_ids));
		}

		########################################################################
		## catalog_products_query — sort by name
		########################################################################

		$products = f::catalog_products_query([
			'categories' => [$category_id],
			'sort' => 'name',
		])->fetch_all();

		if (count($products) >= 2) {
			$first_name = $products[0]['name'];
			$second_name = $products[1]['name'];

			if (strcasecmp($first_name, $second_name) > 0) {
				throw new Exception('catalog_products_query: Sort by name should be alphabetical. Got "'. $first_name .'" before "'. $second_name .'"');
			}
		}

		########################################################################
		## catalog_products_query — sort by price
		########################################################################

		$products = f::catalog_products_query([
			'categories' => [$category_id],
			'sort' => 'price',
		])->fetch_all();

		if (count($products) >= 2) {
			if ((float)$products[0]['final_price'] > (float)$products[1]['final_price']) {
				throw new Exception('catalog_products_query: Sort by price should be ascending');
			}
		}

		########################################################################
		## catalog_products_query — limit
		########################################################################

		$products = f::catalog_products_query([
			'categories' => [$category_id],
			'limit' => 1,
		])->fetch_all();

		if (count($products) > 1) {
			throw new Exception('catalog_products_query: Limit 1 should return at most 1 product, got '. count($products));
		}

		########################################################################
		## catalog_categories_query — returns test category
		########################################################################

		$categories = f::catalog_categories_query(null, [
			'categories' => [$category_id],
		])->fetch_all();

		if (empty($categories)) {
			throw new Exception('catalog_categories_query: Test category should appear in listing');
		}

		########################################################################
		## catalog_products_search_query — keyword match
		########################################################################

		$results = f::catalog_products_search_query([
			'query' => 'Alpha Widget',
		])->fetch_all();

		$found_ids = array_column($results, 'id');

		if (!in_array($product1_id, $found_ids)) {
			throw new Exception('catalog_products_search_query: Should find "Alpha Widget" by name search');
		}

		########################################################################
		## catalog_products_search_query — no match
		########################################################################

		$results = f::catalog_products_search_query([
			'query' => 'xyznonexistent12345',
		])->fetch_all();

		if (!empty($results)) {
			throw new Exception('catalog_products_search_query: Nonsense query should return empty results');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::rollback();

		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."products AUTO_INCREMENT = ". (int)$products_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."categories AUTO_INCREMENT = ". (int)$categories_auto_id .";");
		database::query("ALTER TABLE ". DB_TABLE_PREFIX ."brands AUTO_INCREMENT = ". (int)$brands_auto_id .";");
	}
