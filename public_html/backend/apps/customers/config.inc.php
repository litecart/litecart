<?php

	return [
		'name' => language::translate('title_customers', 'Customers'),
		'default' => 'customers',
		'priority' => 0,

		'theme' => [
			'color' => '#21a261',
			'icon' => 'icon-user',
		],

		'menu' => [
			[
				'title' => language::translate('title_customers', 'Customers'),
				'doc' => 'customers',
				'params' => [],
			],
			[
				'title' => language::translate('title_customer_groups', 'Customer Groups'),
				'doc' => 'customer_groups',
				'params' => [],
			],
			[
				'title' => language::translate('title_newsletter_recipients', 'Newsletter Recipients'),
				'doc' => 'newsletter_recipients',
				'params' => [],
			],
			[
				'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
				'doc' => 'csv',
				'params' => [],
			],
		],

		'docs' => [
			'customer_picker' => 'customer_picker.inc.php',
			'customer_groups' => 'customer_groups.inc.php',
			'customers' => 'customers.inc.php',
			'customers.json' => 'customers.json.inc.php',
			'csv' => 'csv.inc.php',
			'edit_address' => 'edit_address.inc.php',
			'edit_customer' => 'edit_customer.inc.php',
			'edit_customer_group' => 'edit_customer_group.inc.php',
			'get_address.json' => 'get_address.json.inc.php',
			'newsletter_recipients' => 'newsletter_recipients.inc.php',
		],

		'search_results' => 'search_results.inc.php',
	];
