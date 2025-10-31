<?php

	// Define collections
	return [
		[
			'id' => 'translations',
			'entity' => 'translation',
			'name' => t('title_translations', 'Translations'),
			'columns' => [],
		],
		[
			'id' => 'attribute_groups',
			'entity' => 'attribute_group',
			'name' => t('title_attribute_groups', 'Attribute Groups'),
			'columns' => ['name'],
		],
		[
			'id' => 'attribute_values',
			'entity' => 'attribute_value',
			'name' => t('title_attribute_values', 'Attribute Values'),
			'columns' => ['name'],
		],
		[
			'id' => 'brands',
			'entity' => 'brand',
			'name' => t('title_brands', 'Brands'),
			'columns' => ['description', 'short_description', 'head_title', 'meta_description'],
		],
		[
			'id' => 'categories',
			'entity' => 'category',
			'name' => t('title_categories', 'Categories'),
			'columns' => ['name', 'short_description', 'description', 'head_title', 'h1_title', 'meta_description'],
		],
		[
			'id' => 'delivery_statuses',
			'entity' => 'delivery_status',
			'name' => t('title_delivery_statuses', 'Delivery Statuses'),
			'columns' => ['name', 'description'],
		],
		[
			'id' => 'modules',
			'entity' => 'translation',
			'name' => t('title_modules', 'Modules'),
			'columns' => [],
		],
		[
			'id' => 'order_statuses',
			'entity' => 'order_status',
			'name' => t('title_order_statuses', 'Order Statuses'),
			'columns' => ['name', 'description', 'email_subject', 'email_message'],
		],
		[
			'id' => 'pages',
			'entity' => 'page',
			'name' => t('title_pages', 'Pages'),
			'columns' => ['title', 'head_title', 'meta_description', 'content'],
		],
		[
			'id' => 'products',
			'entity' => 'product',
			'name' => t('title_products', 'Products'),
			'columns' => ['name', 'description', 'short_description', 'technical_data', 'head_title', 'meta_description'],
		],
		[
			'id' => 'quantity_units',
			'entity' => 'quantity_unit',
			'name' => t('title_quantity_units', 'Quantity Units'),
			'columns' => ['name', 'description'],
		],
		[
			'id' => 'setting_groups',
			'entity' => 'translation',
			'name' => t('title_setting_groups', 'Setting Groups'),
			'columns' => [],
		],
		[
			'id' => 'settings',
			'entity' => 'translation',
			'name' => t('title_settings', 'Settings'),
			'columns' => [],
		],
		[
			'id' => 'sold_out_statuses',
			'entity' => 'sold_out_status',
			'name' => t('title_sold_out_statuses', 'Sold Out Statuses'),
			'columns' => ['name', 'description'],
		],
	];
