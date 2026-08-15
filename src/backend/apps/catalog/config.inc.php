<?php

	return [
		'name' => t('title_catalog', 'Catalog'),
		'group' => 'website',
		'default' => 'category_tree',
		'priority' => 0,

		'theme' => [
			'color' => '#d4ce12',
			'icon' => 'icon-folder-tree',
		],

		'search_results' => 'search_results.inc.php',

		'menu' => [
			[
				'title' => t('title_category_tree', 'Category Tree'),
				'doc' => 'category_tree',
			],
			[
				'title' => t('title_products', 'Products'),
				'doc' => 'products',
			],
			[
				'title' => t('title_brands', 'Brands'),
				'doc' => 'brands',
			],
			[
				'title' => t('title_suppliers', 'Suppliers'),
				'doc' => 'suppliers',
			],
			[
				'title' => t('title_attributes', 'Attributes'),
				'doc' => 'attribute_groups',
			],
			[
				'title' => t('title_campaigns', 'Campaigns'),
				'doc' => 'campaigns',
			],
			[
				'title' => t('title_stock_items', 'Stock Items'),
				'doc' => 'stock_items',
			],
			[
				'title' => t('title_stock_transactions', 'Stock Transactions'),
				'doc' => 'stock_transactions',
			],
			[
				'title' => t('title_delivery_statuses', 'Delivery Statuses'),
				'doc' => 'delivery_statuses',
			],
			[
				'title' => t('title_sold_out_statuses', 'Sold Out Statuses'),
				'doc' => 'sold_out_statuses',
			],
			[
				'title' => t('title_quantity_units', 'Quantity Units'),
				'doc' => 'quantity_units',
			],
			[
				'title' => t('title_csv_import_export', 'CSV Import/Export'),
				'doc' => 'csv',
			],
		],

		'docs' => [
			'attribute_groups' => 'attribute_groups.inc.php',
			'attribute_values.json' => 'attribute_values.json.inc.php',
			'edit_attribute_group' => 'edit_attribute_group.inc.php',

			'brands' => 'brands.inc.php',
			'edit_brand' => 'edit_brand.inc.php',

			'campaigns' => 'campaigns.inc.php',
			'edit_campaign' => 'edit_campaign.inc.php',

			'category_tree' => 'category_tree.inc.php',
			'category_picker' => 'category_picker.inc.php',
			'categories.json' => 'categories.json.inc.php',
			'edit_category' => 'edit_category.inc.php',

			'csv' => 'csv.inc.php',

			'delivery_statuses' => 'delivery_statuses.inc.php',
			'edit_delivery_status' => 'edit_delivery_status.inc.php',
			'products' => 'products.inc.php',
			'products.json' => 'products.json.inc.php',
			'product_picker' => 'product_picker.inc.php',
			'product_picker_configure' => 'product_picker_configure.inc.php',
			'edit_product' => 'edit_product.inc.php',


			'quantity_units' => 'quantity_units.inc.php',
			'edit_quantity_unit' => 'edit_quantity_unit.inc.php',

			'reviews' => 'reviews.inc.php',
			'edit_review' => 'edit_review.inc.php',

			'sold_out_statuses' => 'sold_out_statuses.inc.php',
			'edit_sold_out_status' => 'edit_sold_out_status.inc.php',

			'stock_items' => 'stock_items.inc.php',
			'stock_items.json' => 'stock_items.json.inc.php',
			'stock_item_picker' => 'stock_item_picker.inc.php',
			'edit_stock_item' => 'edit_stock_item.inc.php',

			'stock_transactions' => 'stock_transactions.inc.php',
			'edit_stock_transaction' => 'edit_stock_transaction.inc.php',

			'suppliers' => 'suppliers.inc.php',
			'edit_supplier' => 'edit_supplier.inc.php',

			'price_lists' => 'price_lists.inc.php',
			'edit_price_list' => 'edit_price_list.inc.php',
		],
	];
