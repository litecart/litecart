<?php

	$toolset = [
		'name' => 'catalog',
		'description' => 'Tools for searching and browsing catalog products.',
		'tools' => [],
	];

	$toolset['tools'][] = [
		'name' => 'search_products',
		'description' => 'Search products by keyword, category, or brand. Returns id, name, price, quantity, status, and image URL.',
		'inputSchema' => [
			'type' => 'object',
			'properties' => [
				'query' => [
					'type' => 'string',
					'description' => 'Search keyword (matches name and product code)',
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
		'function' => function($params) {

			$conditions = [];

			$language_code = language::$selected['code'] ?? 'en';
			$currency_code = settings::get('store_currency_code') ?: 'SEK';

			$sql_name = "json_value(p.name, '$.". database::input($language_code)."')";

			if (!empty($params['query'])) {
				$q = database::input($params['query']);
				$conditions[] = "($sql_name like '%". $q ."%' or p.code like '%". $q ."%')";
			}

			if (isset($params['category_id'])) {
				$conditions[] = "p.id in (select product_id from ". DB_PREFIX ."products_to_categories where category_id = ". (int)$params['category_id'] .")";
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
				"select p.id, p.status, p.code, p.default_image, p.created_at,
				". $sql_name ." as name,
				pp.price,
				pso.quantity
				from ". DB_PREFIX ."products p

				left join (
					select product_id, max(json_value(price, '$.". database::input($currency_code)."')) as price
					from ". DB_PREFIX ."products_prices
					group by product_id
				) pp on (pp.product_id = p.id)

				left join (
					select pso.product_id, sum(si.quantity) as quantity
					from ". DB_PREFIX ."products_stock_options pso
					left join ". DB_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
					group by pso.product_id
				) pso on (pso.product_id = p.id)

				". $sql_where ."
				order by name asc
				limit ". $limit .";"
			)->fetch_all();

			$results = [];
			foreach ($products as $product) {
				$results[] = [
					'id' => (int)$product['id'],
					'name' => $product['name'],
					'sku' => $product['code'],
					'price' => (float)$product['price'],
					'quantity' => (float)$product['quantity'],
					'status' => (int)$product['status'],
					'image' => $product['default_image'] ?: null,
					'created_at' => $product['created_at'],
				];
			}

			return [
				'count' => count($results),
				'products' => $results,
			];
		},
	];

	return $toolset;
