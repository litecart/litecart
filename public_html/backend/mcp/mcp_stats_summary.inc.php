<?php

	return $schema = [
		'name' => 'stats_summary',
		'description' => 'Returns shop statistics: sales totals (month/year/all-time), order counts, averages, top 5 products by quantity, and customer/product/category counts. All monetary values in the store\'s base currency.',
		'inputSchema' => [
			'type' => 'object',
			'properties' => [
				'period' => [
					'type' => 'string',
					'description' => 'Filter period: "month", "year", or "all" (default: "all")',
					'enum' => ['month', 'year', 'all'],
				],
			],
			'required' => [],
		],
	];

	function mcp_stats_summary($params) {

		$period = $params['period'] ?? 'all';
		$currency_code = settings::get('store_currency_code');

		// Get order statuses that count as a sale
		$order_statuses = database::query(
			"select id from ". DB_TABLE_PREFIX ."order_statuses where is_sale;"
		)->fetch_all('id');

		if (empty($order_statuses)) {
			return [
				'period' => $period,
				'currency' => $currency_code,
				'message' => 'No sale order statuses configured',
			];
		}

		$status_list = "'" . implode("', '", $order_statuses) . "'";

		$stats = [
			'period' => $period,
			'currency' => $currency_code,
		];

		// Period filter
		$date_filter = '';
		switch ($period) {
			case 'month':
				$date_filter = "and created_at >= '" . date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y'))) . "'";
				break;
			case 'year':
				$date_filter = "and created_at >= '" . date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y'))) . "'";
				break;
		}

		// Sales summary
		$orders = database::query(
			"select count(id) as num_orders,
				sum(total - total_tax) as total_sales,
				sum(total_tax) as total_tax,
				max(total) as max_order_amount,
				min(total) as min_order_amount
			from ". DB_TABLE_PREFIX ."orders
			where order_status_id in (". $status_list .")
			". $date_filter .";"
		)->fetch();

		$stats['sales'] = [
			'total' => round((float)$orders['total_sales'], 2),
			'tax' => round((float)$orders['total_tax'], 2),
			'orders' => (int)$orders['num_orders'],
			'average_order' => $orders['num_orders'] > 0 ? round($orders['total_sales'] / $orders['num_orders'], 2) : 0,
			'max_order' => round((float)$orders['max_order_amount'], 2),
			'min_order' => round((float)$orders['min_order_amount'], 2),
		];

		// Top products (by quantity sold)
		$top_products = database::query(
			"select oi.product_id, oi.name, sum(oi.quantity) as total_quantity, sum(oi.price * oi.quantity) as total_revenue
			from ". DB_TABLE_PREFIX ."orders_items oi
			left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
			where o.order_status_id in (". $status_list .")
			". str_replace('created_at', 'o.created_at', $date_filter) ."
			group by oi.product_id, oi.name
			order by total_quantity desc
			limit 5;"
		)->fetch_all();

		$stats['top_products'] = [];
		foreach ($top_products as $product) {
			$stats['top_products'][] = [
				'id' => (int)$product['product_id'],
				'name' => $product['name'],
				'quantity_sold' => (int)$product['total_quantity'],
				'revenue' => round((float)$product['total_revenue'], 2),
			];
		}

		// Counts
		$stats['counts'] = [
			'customers' => (int)database::query(
				"select count(id) as n from ". DB_TABLE_PREFIX ."customers;"
			)->fetch('n'),
			'products' => (int)database::query(
				"select count(id) as n from ". DB_TABLE_PREFIX ."products where status;"
			)->fetch('n'),
			'categories' => (int)database::query(
				"select count(id) as n from ". DB_TABLE_PREFIX ."categories where status;"
			)->fetch('n'),
		];

		return $stats;
	}
