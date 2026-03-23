<?php

	return $schema = [
		'name' => 'products_search',
		'description' => 'Search products by keyword, category, or brand. Returns id, name, price, quantity, status, and image URL.',
		'inputSchema' => [
			'type' => 'object',
			'properties' => [
				'query' => [
					'type' => 'string',
					'description' => 'Search keyword (matches name, sku, mpn, gtin)',
				],
				'category_id' => [
					'type' => 'integer',
					'description' => 'Filter by category ID',
				],
				'brand_id' => [
					'type' => 'integer',
					'description' => 'Filter by brand ID',
				],
				'status' => [
					'type' => 'integer',
					'description' => 'Filter by status (1 = active, 0 = inactive). Default: all',
					'enum' => [0, 1],
				],
				'limit' => [
					'type' => 'integer',
					'description' => 'Max results (default: 10, max: 50)',
				],
			],
			'required' => [],
		],
	];

	function mcp_products_search($params) {

		$conditions = [];

		if (!empty($params['query'])) {
			$q = database::input($params['query']);
			$conditions[] = "(pi.name like '%". $q ."%' or p.sku like '%". $q ."%' or p.mpn like '%". $q ."%' or p.gtin like '%". $q ."%')";
		}

		if (isset($params['category_id'])) {
			$conditions[] = "p.id in (select product_id from ". DB_TABLE_PREFIX ."products_to_categories where category_id = ". (int)$params['category_id'] .")";
		}

		if (isset($params['brand_id'])) {
			$conditions[] = "p.brand_id = ". (int)$params['brand_id'];
		}

		if (isset($params['status'])) {
			$conditions[] = "p.status = ". (int)$params['status'];
		}

		$limit = min((int)($params['limit'] ?? 10), 50);
		if ($limit < 1) $limit = 10;

		$sql_where = $conditions ? 'where ' . implode(' and ', $conditions) : '';

		$products = database::query(
			"select p.id, pi.name, p.sku, p.default_image, p.quantity, p.status, p.date_created
			from ". DB_TABLE_PREFIX ."products p
			left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
			". $sql_where ."
			order by pi.name asc
			limit ". $limit .";"
		)->fetch_all();

		$results = [];
		foreach ($products as $product) {
			$results[] = [
				'id' => (int)$product['id'],
				'name' => $product['name'],
				'sku' => $product['sku'],
				'quantity' => (float)$product['quantity'],
				'status' => (int)$product['status'],
				'image' => $product['default_image'] ?: null,
				'created' => $product['date_created'],
			];
		}

		return [
			'count' => count($results),
			'products' => $results,
		];
	}
