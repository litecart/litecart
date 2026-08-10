<?php

	return [
		'name' => t('title_reports', 'Reports'),
		'group' => 'website',
		'default' => 'monthly_sales',
		'priority' => 0,

		'theme' => [
			'color' => '#b97631',
			'icon' => 'icon-chart-bar',
		],

		'menu' => [
			[
				'title' => t('title_monthly_sales', 'Monthly Sales'),
				'doc' => 'monthly_sales',
				'params' => [],
			],
			[
				'title' => t('title_most_sold_products', 'Most Sold Products'),
				'doc' => 'most_sold_products',
				'params' => [],
			],
			[
				'title' => t('title_most_shopping_customers', 'Most Shopping Customers'),
				'doc' => 'most_shopping_customers',
				'params' => [],
			],
			[
				'title' => t('title_who_purchased', 'Who Purchased'),
				'doc' => 'who_purchased',
				'params' => [],
			],
		],

		'docs' => [
			'monthly_sales' => 'monthly_sales.inc.php',
			'most_sold_products' => 'most_sold_products.inc.php',
			'most_shopping_customers' => 'most_shopping_customers.inc.php',
			'who_purchased' => 'who_purchased.inc.php',
		],
	];
