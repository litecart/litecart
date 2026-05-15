<?php

	$toolset = [
		'name' => 'orders',
		'description' => 'Tools for viewing and managing orders.',
		'tools' => [],
	];

	$toolset['tools'][] = [
		'name' => 'list_orders',
		'description' => 'List recent orders with status, customer, total, and date. Supports filtering by status, customer, and date range.',
		'inputSchema' => [
			'type' => 'object',
			'properties' => [
				'order_status_id' => [
					'type' => 'integer',
					'description' => 'Filter by order status ID',
				],
				'customer_id' => [
					'type' => 'integer',
					'description' => 'Filter by customer ID',
				],
				'date_from' => [
					'type' => 'string',
					'description' => 'Filter orders created on or after this date (YYYY-MM-DD)',
				],
				'date_to' => [
					'type' => 'string',
					'description' => 'Filter orders created on or before this date (YYYY-MM-DD)',
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

			if (isset($params['order_status_id'])) {
				$conditions[] = "o.order_status_id = ". (int)$params['order_status_id'];
			}

			if (isset($params['customer_id'])) {
				$conditions[] = "o.customer_id = ". (int)$params['customer_id'];
			}

			if (!empty($params['date_from'])) {
				$conditions[] = "o.created_at >= '". database::input($params['date_from']) ." 00:00:00'";
			}

			if (!empty($params['date_to'])) {
				$conditions[] = "o.created_at <= '". database::input($params['date_to']) ." 23:59:59'";
			}

			$limit = min((int)($params['limit'] ?? 10), 50);
			if ($limit < 1) $limit = 10;

			$sql_where = $conditions ? 'where ' . implode(' and ', $conditions) : '';

			$orders = database::query(
				"select o.id, o.customer_id, o.customer_firstname, o.customer_lastname, o.customer_email,
					o.total, o.total_tax, o.currency_code, o.order_status_id, os.name as order_status,
					o.created_at
				from ". DB_TABLE_PREFIX ."orders o
				left join ". DB_TABLE_PREFIX ."order_statuses_info os on (os.order_status_id = o.order_status_id and os.language_code = '". database::input(language::$selected['code']) ."')
				". $sql_where ."
				order by o.created_at desc
				limit ". $limit .";"
			)->fetch_all();

			$results = [];
			foreach ($orders as $order) {
				$results[] = [
					'id' => (int)$order['id'],
					'customer' => [
						'id' => (int)$order['customer_id'],
						'name' => trim($order['customer_firstname'] . ' ' . $order['customer_lastname']),
						'email' => $order['customer_email'],
					],
					'total' => round((float)$order['total'], 2),
					'tax' => round((float)$order['total_tax'], 2),
					'currency' => $order['currency_code'],
					'status' => $order['order_status'],
					'status_id' => (int)$order['order_status_id'],
					'created' => $order['created_at'],
				];
			}

			return [
				'count' => count($results),
				'orders' => $results,
			];
		},
	];

	return $toolset;
