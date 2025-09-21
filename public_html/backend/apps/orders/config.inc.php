<?php

	return [
		'name' => t('title_orders', 'Orders'),
		'group' => 'website',
		'default' => 'orders',
		'priority' => 0,

		'theme' => [
			'color' => '#8fc722',
			'icon' => 'icon-orders',
		],
		'menu' => [
			[
				'title' => t('title_orders', 'Orders'),
				'doc' => 'orders',
				'params' => [],
			],
			[
				'title' => t('title_order_statuses', 'Order Statuses'),
				'doc' => 'order_statuses',
				'params' => [],
			],
		],

		'docs' => [
			'order' => 'order.inc.php',
			'orders' => 'orders.inc.php',
			'edit_order' => 'edit_order.inc.php',
			'order_statuses' => 'order_statuses.inc.php',
			'edit_order_status' => 'edit_order_status.inc.php',
			'add_product' => 'add_product.inc.php',
		],

		'search_results' => 'search_results.inc.php',
	];
