<?php

return [
	[
		'name' => t('title_customers', 'Customers'),
		'entity' => 'customer',
		'tables' => [
			'c' => '`' . DB_TABLE_PREFIX . 'customers`',
		],
		'bindings' => [],
	],

	[
		'name' => t('title_orders', 'Orders'),
		'entity' => 'order',
		'tables' => [
			'i' => '`' . DB_TABLE_PREFIX . 'invoices`',
			'il' => '`' . DB_TABLE_PREFIX . 'invoices_lines`',
		],
		'bindings' => ['il.invoice_id = i.id'],
	],
];
