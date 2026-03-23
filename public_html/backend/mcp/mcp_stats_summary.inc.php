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
					'enum' => ['month', 'year', 'alltime'],
				],
			],
			'required' => [],
		],
	];

	function mcp_stats_summary($params) {

		$period = fallback($params['period'], 'all');
		$currency_code = settings::get('store_currency_code');

		// Get order statuses that count as a sale
		$order_statuses = database::query(
			"select id from ". DB_TABLE_PREFIX ."order_statuses where is_sale;"
		)->fetch_all('id');

		if (!$order_statuses) {
			return [
				'period' => $period,
				'currency_code' => $currency_code,
				'message' => 'No sale order statuses configured',
			];
		}

		$result = [
			'period' => $period,
			'currency_code' => $currency_code,
		];

		// Period filter
		$sql_date_filter = '';
		switch ($period) {
			case 'month':
				$sql_date_filter = "and created_at >= '" . date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y'))) . "'";
				break;
			case 'year':
				$sql_date_filter = "and created_at >= '" . date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y'))) . "'";
				break;
		}

		// Sales summary
		$result['sales'] = database::query(
			"select count(id) as num_orders,
				sum(total - total_tax) as total_sales,
				sum(total_tax) as total_tax,
				max(total) as max_order_amount,
				min(total) as min_order_amount
			from ". DB_TABLE_PREFIX ."orders
			where order_status_id in ('" . implode("', '", database::input($order_statuses)) . "')
			". $sql_date_filter .";"
		)->fetch(function($orders) {
			return [
				'total' => round((float)$orders['total_sales'], 2),
				'tax' => round((float)$orders['total_tax'], 2),
				'orders' => (int)$orders['num_orders'],
				'average_order' => $orders['num_orders'] > 0 ? round($orders['total_sales'] / $orders['num_orders'], 2) : 0,
				'max_order' => round((float)$orders['max_order_amount'], 2),
				'min_order' => round((float)$orders['min_order_amount'], 2),
			];
		});

		// Most sold products
		$result['most_sold_products'] = database::query(
			"select oi.product_id, oi.name, sum(oi.quantity) as total_quantity, sum(oi.price * oi.quantity) as total_revenue
			from ". DB_TABLE_PREFIX ."orders_items oi
			left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
			where o.order_status_id in ('" . implode("', '", database::input($order_statuses)) . "')
			". str_replace('created_at', 'o.created_at', $sql_date_filter) ."
			group by oi.product_id, oi.name
			order by total_quantity desc
			limit 5;"
		)->fetch_all(function($products) {
			return [
				'product_id' => (int)$products['product_id'],
				'name' => $products['name'],
				'total_quantity' => (int)$products['total_quantity'],
				'total_revenue' => round((float)$products['total_revenue'], 2),
			];
		});

		// Counts
		$result['counts'] = [
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

		return $result;
	}
