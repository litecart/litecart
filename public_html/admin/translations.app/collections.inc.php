<?php

// Define collections
  return [
    [
      'id' => 'translations',
      'entity' => 'translation',
      'name' => language::translate('title_translations', 'Translations'),
      'entity_table' => false,
      'info_table' => false,
      'entity_column' => false,
      'info_columns' => [],
    ],
    [
      'id' => 'attribute_groups',
      'entity' => 'attribute_group',
      'name' => language::translate('title_attribute_groups', 'Attribute Groups'),
      'entity_table' => 'attribute_groups_info',
      'info_table' => 'attribute_groups_info',
      'entity_column' => 'group_id',
      'info_columns' => ['name'],
    ],
    [
      'id' => 'attribute_values',
      'entity' => 'attribute_value',
      'name' => language::translate('title_attribute_values', 'Attribute Values'),
      'entity_table' => 'attribute_values',
      'info_table' => 'attribute_values_info',
      'entity_column' => 'value_id',
      'info_columns' => ['name'],
    ],
    [
      'id' => 'categories',
      'entity' => 'category',
      'name' => language::translate('title_categories', 'Categories'),
      'entity_table' => 'categories',
      'info_table' => 'categories_info',
      'entity_column' => 'category_id',
      'info_columns' => ['name', 'short_description', 'description', 'head_title', 'h1_title', 'meta_description'],
    ],
    [
      'id' => 'delivery_statuses',
      'entity' => 'delivery_status',
      'name' => language::translate('title_delivery_statuses', 'Delivery Statuses'),
      'entity_table' => 'delivery_statuses',
      'info_table' => 'delivery_statuses_info',
      'entity_column' => 'delivery_status_id',
      'info_columns' => ['name', 'description'],
    ],
    [
      'id' => 'modules',
      'entity' => 'translation',
      'name' => language::translate('title_modules', 'Modules'),
      'entity_table' => false,
      'info_table' => false,
      'entity_column' => false,
      'info_columns' => [],
    ],
    [
      'id' => 'manufacturers',
      'entity' => 'manufacturer',
      'name' => language::translate('title_manufacturers', 'Manufacturers'),
      'entity_table' => 'manufacturers',
      'info_table' => 'manufacturers_info',
      'entity_column' => 'manufacturer_id',
      'info_columns' => ['description', 'short_description', 'head_title', 'meta_description'],
    ],
    [
      'id' => 'order_statuses',
      'entity' => 'order_status',
      'name' => language::translate('title_order_statuses', 'Order Statuses'),
      'entity_table' => 'order_statuses',
      'info_table' => 'order_statuses_info',
      'entity_column' => 'order_status_id',
      'info_columns' => ['name', 'description', 'email_subject', 'email_message'],
    ],
    [
      'id' => 'pages',
      'entity' => 'page',
      'name' => language::translate('title_pages', 'Pages'),
      'entity_table' => 'pages',
      'info_table' => 'pages_info',
      'entity_column' => 'page_id',
      'info_columns' => ['title', 'head_title', 'meta_description', 'content'],
    ],
    [
      'id' => 'products',
      'entity' => 'product',
      'name' => language::translate('title_products', 'Products'),
      'entity_table' => 'products',
      'info_table' => 'products_info',
      'entity_column' => 'product_id',
      'info_columns' => ['name', 'description', 'short_description', 'technical_data', 'head_title', 'meta_description'],
    ],
    [
      'id' => 'quantity_units',
      'entity' => 'quantity_unit',
      'name' => language::translate('title_quantity_units', 'Quantity Units'),
      'entity_table' => 'quantity_units',
      'info_table' => 'quantity_units_info',
      'entity_column' => 'quantity_unit_id',
      'info_columns' => ['name', 'description'],
    ],
    [
      'id' => 'setting_groups',
      'entity' => 'translation',
      'name' => language::translate('title_setting_groups', 'Setting Groups'),
      'entity_table' => false,
      'info_table' => false,
      'entity_column' => false,
      'info_columns' => [],
    ],
    [
      'id' => 'settings',
      'entity' => 'translation',
      'name' => language::translate('title_settings', 'Settings'),
      'entity_table' => false,
      'info_table' => false,
      'entity_column' => false,
      'info_columns' => [],
    ],
    [
      'id' => 'slides',
      'entity' => 'slide',
      'name' => language::translate('title_slides', 'Slides'),
      'entity_table' => 'slides',
      'info_table' => 'slides_info',
      'entity_column' => 'slide_id',
      'info_columns' => ['caption'],
    ],
    [
      'id' => 'sold_out_statuses',
      'entity' => 'sold_out_status',
      'name' => language::translate('title_sold_out_statuses', 'Sold Out Statuses'),
      'entity_table' => 'sold_out_statuses',
      'info_table' => 'sold_out_statuses_info',
      'entity_column' => 'sold_out_status_id',
      'info_columns' => ['name', 'description'],
    ],
  ];