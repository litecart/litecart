<?php

	return [
		'name' => language::translate('title_modules', 'Modules'),
		'default' => 'customer',
		'priority' => 0,

		'theme' => [
			'color' => '#c449c5',
			'icon' => 'icon-cube',
		],

		'menu' => [
			[
				'title' => language::translate('title_customer_modules', 'Customer Modules'),
				'doc' => 'customer',
			],
			[
				'title' => language::translate('title_shipping_modules', 'Shipping Modules'),
				'doc' => 'shipping',
			],
			[
				'title' => language::translate('title_payment_modules', 'Payment Modules'),
				'doc' => 'payment',
			],
			[
				'title' => language::translate('title_order_modules', 'Order Modules'),
				'doc' => 'order',
			],
			[
				'title' => language::translate('title_order_total_modules', 'Order Total Modules'),
				'doc' => 'order_total',
			],
			[
				'title' => language::translate('title_job_modules', 'Job Modules'),
				'doc' => 'jobs',
			],
		],

		'docs' => [
			'customer' => 'modules.inc.php',
			'order' => 'modules.inc.php',
			'order_total' => 'modules.inc.php',
			'payment' => 'modules.inc.php',
			'shipping' => 'modules.inc.php',
			'jobs' => 'modules.inc.php',
			'edit_customer' => 'edit_module.inc.php',
			'edit_job' => 'edit_module.inc.php',
			'edit_order' => 'edit_module.inc.php',
			'edit_order_total' => 'edit_module.inc.php',
			'edit_payment' => 'edit_module.inc.php',
			'edit_shipping' => 'edit_module.inc.php',
			'run_job' => 'run_job.inc.php',
		],
	];
