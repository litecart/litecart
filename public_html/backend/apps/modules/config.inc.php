<?php

	return [
		'name' => t('title_modules', 'Modules'),
		'group' => 'system',
		'default' => 'customer',
		'priority' => 0,

		'theme' => [
			'color' => '#c449c5',
			'icon' => 'icon-cube',
		],

		'menu' => [
			[
				'title' => t('title_customer_modules', 'Customer Modules'),
				'doc' => 'customer',
			],
			[
				'title' => t('title_shipping_modules', 'Shipping Modules'),
				'doc' => 'shipping',
			],
			[
				'title' => t('title_payment_modules', 'Payment Modules'),
				'doc' => 'payment',
			],
			[
				'title' => t('title_order_modules', 'Order Modules'),
				'doc' => 'order',
			],
			[
				'title' => t('title_job_modules', 'Job Modules'),
				'doc' => 'jobs',
			],
		],

		'docs' => [
			'customer' => 'modules.inc.php',
			'order' => 'modules.inc.php',
			'payment' => 'modules.inc.php',
			'shipping' => 'modules.inc.php',
			'jobs' => 'modules.inc.php',
			'edit_customer' => 'edit_module.inc.php',
			'edit_job' => 'edit_module.inc.php',
			'edit_order' => 'edit_module.inc.php',
			'edit_payment' => 'edit_module.inc.php',
			'edit_shipping' => 'edit_module.inc.php',
			'run_job' => 'run_job.inc.php',
		],
	];
