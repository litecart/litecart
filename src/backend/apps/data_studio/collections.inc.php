<?php

return [
	[
		'name' => t('title_customers', 'Customers'),
		'entity' => 'customer',
		'tables' => [
			'c' => '`' . DB_PREFIX . 'customers`',
		],
		'bindings' => [],
	],

	[
		'name' => t('title_orders', 'Orders'),
		'entity' => 'order',
		'tables' => [
			'i' => '`' . DB_PREFIX . 'invoices`',
			'il' => '`' . DB_PREFIX . 'invoices_lines`',
		],
		'bindings' => ['il.invoice_id = i.id'],
	],
];
