<?php

	return [
		'name' => language::translate('title_catalog', 'Catalog'),
		'default' => 'category_tree',
		'priority' => 0,

		'theme' => [
			'color' => '#d4ce12',
			'icon' => 'fa-th',
		],

		'search_results' => 'search_results.inc.php',

		'menu' => [
			[
				'title' => language::translate('title_category_tree', 'Category Tree'),
				'doc' => 'category_tree',
				'params' => [],
			],
			[
				'title' => language::translate('title_products', 'Products'),
				'doc' => 'products',
				'params' => [],
			],
			[
				'title' => language::translate('title_brands', 'Brands'),
				'doc' => 'brands',
				'params' => [],
			],
			[
				'title' => language::translate('title_suppliers', 'Suppliers'),
				'doc' => 'suppliers',
				'params' => [],
			],
			[
				'title' => language::translate('title_attributes', 'Attributes'),
				'doc' => 'attribute_groups',
				'params' => [],
			],
			[
				'title' => language::translate('title_campaigns', 'Campaigns'),
				'doc' => 'campaigns',
				'params' => [],
			],
			[
				'title' => language::translate('title_stock_transactions', 'Stock Transactions'),
				'doc' => 'stock_transactions',
				'params' => [],
			],
			[
				'title' => language::translate('title_delivery_statuses', 'Delivery Statuses'),
				'doc' => 'delivery_statuses',
				'params' => [],
			],
			[
				'title' => language::translate('title_sold_out_statuses', 'Sold Out Statuses'),
				'doc' => 'sold_out_statuses',
				'params' => [],
			],
			[
				'title' => language::translate('title_quantity_units', 'Quantity Units'),
				'doc' => 'quantity_units',
				'params' => [],
			],
			[
				'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
				'doc' => 'csv',
				'params' => [],
			],
		],
		'docs' => [
			'attribute_groups' => 'attribute_groups.inc.php',
			'attribute_values.json' => 'attribute_values.json.inc.php',
			'campaigns' => 'campaigns.inc.php',
			'category_tree' => 'category_tree.inc.php',
			'edit_attribute_group' => 'edit_attribute_group.inc.php',
			'edit_product' => 'edit_product.inc.php',
			'edit_product2' => 'edit_product2.inc.php',
			'edit_product3' => 'edit_product3.inc.php',
			'edit_category' => 'edit_category.inc.php',
			'brands' => 'brands.inc.php',
			'edit_brand' => 'edit_brand.inc.php',
			'campaigns' => 'campaigns.inc.php',
			'edit_campaign' => 'edit_campaign.inc.php',
			'suppliers' => 'suppliers.inc.php',
			'edit_supplier' => 'edit_supplier.inc.php',
			'delivery_statuses' => 'delivery_statuses.inc.php',
			'edit_delivery_status' => 'edit_delivery_status.inc.php',
			'sold_out_statuses' => 'sold_out_statuses.inc.php',
			'edit_sold_out_status' => 'edit_sold_out_status.inc.php',
			'quantity_units' => 'quantity_units.inc.php',
			'edit_quantity_unit' => 'edit_quantity_unit.inc.php',
			'csv' => 'csv.inc.php',
			'category_picker' => 'category_picker.inc.php',
			'categories.json' => 'categories.json.inc.php',
			'products' => 'products.inc.php',
			'products.json' => 'products.json.inc.php',
			'product_picker' => 'product_picker.inc.php',
			'product_picker_configure' => 'product_picker_configure.inc.php',
			'stock_transactions' => 'stock_transactions.inc.php',
			'edit_stock_transaction' => 'edit_stock_transaction.inc.php',
		],
	];
