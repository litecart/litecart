INSERT INTO `lc_attribute_groups` (`id`, `code`, `name`, `sort`, `updated_at`, `created_at`) VALUES
(1, 'size', '{"en": "Size"}', 'alphabetical', NOW(), NOW()),
(2, 'color', '{"en": "Color"}', 'priority', NOW(), NOW());
-- -----
INSERT INTO `lc_attribute_values` (`id`, `group_id`, `name`, `updated_at`, `created_at`) VALUES
(1, 1, '{"en": "Small"}', NOW(), NOW()),
(2, 1, '{"en": "Medium"}', NOW(), NOW()),
(3, 1, '{"en": "Large"}', NOW(), NOW()),
(4, 2, '{"en": "Yellow"}', NOW(), NOW()),
(5, 2, '{"en": "Green"}', NOW(), NOW()),
(6, 2, '{"en": "Red"}', NOW(), NOW()),
(7, 2, '{"en": "Blue"}', NOW(), NOW()),
(8, 2, '{"en": "Purple"}', NOW(), NOW());
-- -----
INSERT INTO `lc_brands` (`id`, `status`, `featured`, `code`, `name`, `keywords`, `image`, `updated_at`, `created_at`) VALUES
(1, 1, 1, 'acme', 'ACME Corp.', '', 'brands/1-acme-corp.png', NOW(), NOW());
-- -----
INSERT INTO `lc_campaigns` (`id`, `status`, `name`, `updated_at`, `created_at`) VALUES
(1, 1, 'Super Sale', NOW(), NOW());
-- -----
INSERT INTO `lc_campaigns_products` (`id`, `product_id`, `price`) VALUES
(1, 1, '{"USD": 18.00}');
-- -----
INSERT INTO `lc_categories` (`id`, `parent_id`, `status`, `code`, `name`, `priority`, `updated_at`, `created_at`) VALUES
(1, 0, 1, '', '{"en": "Rubber Ducks"}', 0, NOW(), NOW()),
(2, 1, 1, '', '{"en": "Subcategory"}', 0, NOW(), NOW());
-- -----
UPDATE `lc_categories`
SET `name` = '{"en": "Rubber Ducks"}',
	`short_description` = '{"en": "Lorem ipsum dolor sit amet, consectetur adipiscing elit."}',
	`description` = '{"en": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas nibh arcu, facilisis ac pharetra eu, porta semper ligula. Nulla dapibus nulla vehicula elit finibus bibendum. Maecenas porta neque vel pharetra accumsan. Pellentesque ut leo in leo pulvinar ullamcorper sit amet at lorem. Nulla mollis urna sed metus dapibus, nec laoreet elit tincidunt. Proin nec vestibulum elit. Duis at ipsum vitae lacus dignissim condimentum id posuere purus. Aliquam semper sit amet lacus in euismod. Suspendisse ornare faucibus nibh, a tincidunt tortor aliquam vel. Nulla vel nulla nunc. Morbi dignissim rutrum lectus, sit amet luctus purus lacinia ac. Integer nulla nulla, porttitor vel nunc non, consequat ullamcorper nunc. Nunc ornare quis leo sed consequat. Donec sed maximus felis."}'
WHERE `id` = 1;
-- -----
INSERT INTO `lc_categories_filters` (`id`, `category_id`, `select_multiple`, `attribute_group_id`, `priority`) VALUES
(1, 1, 1, 1, 1);
-- -----
INSERT INTO `lc_customers` (`id`, `code`, `status`, `email`, `tax_id`, `company`, `firstname`, `lastname`, `address1`, `address2`, `postcode`, `city`, `country_code`, `zone_code`, `phone`, `different_shipping_address`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_zone_code`, `shipping_phone`, `updated_at`, `created_at`) VALUES
(1, '', 1, 'user@email.com', '0000000000', 'ACME Corp.', 'John', 'Doe', 'Longway Street 1', '', '12345', 'Newtown', 'US', 'CA', '1-555-123-4567', 0, '', '', '', '', '', '', '', '', '', '', NOW(), NOW());
-- -----
INSERT INTO `lc_modules` (`module_id`, `type`, `status`, `priority`, `settings`, `last_log`, `updated_at`, `created_at`) VALUES
('sm_zone_weight', 'shipping', 0, 0, '{"status":"1","icon":"","weight_unit":"kg","geo_zone_id_1":"","weight_rate_table_1":"","geo_zone_id_2":"","weight_rate_table_2":"","geo_zone_id_3":"","weight_rate_table_3":"","weight_rate_table_x":"5:8.95;10:15.95","method":">=","handling_fee":"0.00","tax_class_id":"1","priority":"0"}', '', NOW(), NOW()),
('pm_cod', 'payment', 0, 0, '{"status":"1","icon":"","fee":"5.00","tax_class_id":"1","order_status_id":"2","geo_zone_id":"","priority":"0"}', '', NOW(), NOW());
-- -----
INSERT INTO `lc_orders` (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_zone_code`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_zone_code`, `shipping_phone`, `shipping_option_id`, `shipping_option_name`, `shipping_tracking_id`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight_total`, `weight_unit`, `currency_code`, `currency_value`, `subtotal`, `subtotal_tax`, `total`, `total_tax`, `updated_at`, `created_at`) VALUES
(1, 2, 1, 'ACME Corp.', 'John', 'Doe', 'user@email.com', '1-555-123-4567', '', 'Longway Street 1', '', 'Newtown', '12345', 'US', 'CA', 'ACME Corp.', 'John', 'Doe', 'Longway Street 1', '', 'Newtown', '12345', 'US', 'CA', '', 'sm_vendor:parcel', 'Domestic Parcel', '1112223334', 'pm_vendor:card', 'Card Payment', '123456789', 'en', '1.00', 'kg', 'USD', 1, 8, 0, 7.2, 0, NOW(), NOW());
-- -----
INSERT INTO `lc_orders_comments` (`id`, `order_id`, `author`, `text`, `hidden`, `created_at`) VALUES
(1, 1, 'customer', 'This is a message from the customer.', 0, NOW()),
(2, 1, 'staff', 'This is a message from the store crew.', 0, NOW()),
(3, 1, 'staff', 'This is a hidden message by the store crew.', 1, NOW()),
(4, 1, 'system', 'Order status changed to Dispatched', 1, NOW());
-- -----
INSERT INTO `lc_orders_items` (`id`, `order_id`, `product_id`, `stock_option_id`, `userdata`, `name`, `sku`, `gtin`, `quantity`, `price`, `tax`, `weight`, `weight_unit`) VALUES
(1, 1, 1, 1, '', 'Yellow Duck', 'RD001-S', '4006381333931', 1, 8.00, 0, 1.00, 'kg');
-- -----
INSERT INTO `lc_products` (`id`, `status`, `brand_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `default_category_id`, `code`, `name`, `quantity_unit_id`, `tax_class_id`, `image`, `views`, `purchases`, `date_valid_from`, `date_valid_to`, `updated_at`, `created_at`) VALUES
(1, 1, 1, 0, 1, 2, 2, 'rd001', '{"en": "Yellow Duck"}', 1, 1, 'products/1-yellow-duck-1.jpg', 0, 0, NULL, NULL, NOW(), NOW()),
(2, 1, 1, 0, 1, 2, 2, 'rd002', '{"en": "Green Duck"}', 1, 1, 'products/2-green-duck-1.jpg', 0, 0, NULL, NULL, NOW(), NOW()),
(3, 1, 1, 0, 1, 2, 1, 'rd003', '{"en": "Red Duck"}', 1, 1, 'products/3-red-duck-1.jpg', 0, 0, NULL, NULL, NOW(), NOW()),
(4, 1, 1, 0, 1, 2, 1, 'rd004', '{"en": "Blue Duck"}', 1, 1, 'products/4-blue-duck-1.jpg', 0, 0, NULL, NULL, NOW(), NOW()),
(5, 1, 1, 0, 1, 2, 1, 'rd005', '{"en": "Purple Duck"}', 1, 1, 'products/5-purple-duck-1.jpg', 0, 0, NULL, NULL, NOW(), NOW());
-- -----
UPDATE `lc_products` SET `description` = '{"en": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue. <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue. Cras scelerisque dui non consequat sollicitudin. Sed pretium tortor ac auctor molestie. Nulla facilisi. Maecenas pulvinar nibh vitae lectus vehicula semper. Donec et aliquet velit. Curabitur non ullamcorper mauris. In hac habitasse platea dictumst. Phasellus ut pretium justo, sit amet bibendum urna. Maecenas sit amet arcu pulvinar, facilisis quam at, viverra nisi. Morbi sit amet adipiscing ante. Integer imperdiet volutpat ante, sed venenatis urna volutpat a. Proin justo massa, convallis vitae consectetur sit amet, facilisis id libero.</p>"}';
-- -----
UPDATE `lc_products` SET `technical_data` = '{"en": "Colors\\nBody: Yellow\\nEyes: Black\\nBeak: Orange\\n\\nOther\\nMaterial: Plastic"}' WHERE `id` = 1;
-- -----
UPDATE `lc_products` SET `technical_data` = '{"en": "Colors\\nBody: Green\\nEyes: Black\\nBeak: Orange\\n\\nOther\\nMaterial: Plastic"}' WHERE `id` = 2;
-- -----
UPDATE `lc_products` SET `technical_data` = '{"en": "Colors\\nBody: Red\\nEyes: Black\\nBeak: Orange\\n\\nOther\\nMaterial: Plastic"}' WHERE `id` = 3;
-- -----
UPDATE `lc_products` SET `technical_data` = '{"en": "Colors\\nBody: Blue\\nEyes: Black\\nBeak: Orange\\n\\nOther\\nMaterial: Plastic"}' WHERE `id` = 4;
-- -----
UPDATE `lc_products` SET `technical_data` = '{"en": "Colors\\nBody: Purple\\nEyes: Black\\nBeak: Orange\\n\\nOther\\nMaterial: Plastic"}' WHERE `id` = 5;
-- -----
INSERT INTO `lc_products_attributes` (`product_id`, `group_id`, `value_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5);
-- -----
INSERT INTO `lc_products_to_categories` (`product_id`, `category_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(4, 1),
(5, 1);
-- -----
INSERT INTO `lc_products_images` (`id`, `product_id`, `filename`, `priority`) VALUES
(1, 1, 'products/1-yellow-duck-1.webp', 1),
(2, 2, 'products/2-green-duck-1.webp', 1),
(3, 3, 'products/3-red-duck-1.webp', 1),
(4, 4, 'products/4-blue-duck-1.webp', 1),
(5, 5, 'products/5-purple-duck-1.webp', 1);
-- -----
INSERT INTO `lc_products_stock_options` (`id`, `product_id`, `stock_item_id`, `price_modifier`, `price_adjustment`) VALUES
(1, 1, 1, '+', 0.00),
(2, 1, 2, '+', 0.00),
(3, 1, 3, '+', 0.00);
-- -----
INSERT INTO `lc_products_prices` (`id`, `product_id`, `price`) VALUES
(1, 1, '{"USD": 20.00}'),
(2, 2, '{"USD": 20.00}'),
(3, 3, '{"USD": 20.00}'),
(4, 4, '{"USD": 20.00}'),
(5, 5, '{"USD": 20.00}');
-- -----
INSERT INTO `lc_stock_items` (`id`, `brand_id`, `sku`, `gtin`, `name`, `image`, `weight`, `weight_unit`, `length`, `width`, `height`, `length_unit`, `quantity`, `backordered`, `priority`, `updated_at`, `created_at`) VALUES
(1, 1, 'RD001-S', '4006381333931', '{"en": "Yellow Duck (Small)"}', 'stock_items/1-yellow-duck-1.webp', 1.0000, 'kg', 10.0000, 10.0000, 10.0000, 'cm', 50.0000, 0.0000, 1, NOW(), NOW()),
(2, 1, 'RD001-M', '4006381333932', '{"en": "Yellow Duck (Medium)"}', 'stock_items/2-yellow-duck-1.webp', 1.0000, 'kg', 12.0000, 12.0000, 12.0000, 'cm', 50.0000, 0.0000, 2, NOW(), NOW()),
(3, 1, 'RD001-L', '4006381333933', '{"en": "Yellow Duck (Large)"}', 'stock_items/3-yellow-duck-1.webp', 1.0000, 'kg', 15.0000, 15.0000, 15.0000, 'cm', 0.0000, 50.0000, 3, NOW(), NOW());
-- -----
UPDATE `lc_settings` SET `value` = 2 WHERE `key` = 'cookie_policy';
-- -----
UPDATE `lc_settings` SET `value` = 3 WHERE `key` = 'privacy_policy';
-- -----
UPDATE `lc_settings` SET `value` = 4 WHERE `key` = 'terms_of_purchase';
