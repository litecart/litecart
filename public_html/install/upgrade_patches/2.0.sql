CREATE TABLE IF NOT EXISTS `lc_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` varchar(64) NOT NULL,
  `type` varchar(16) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  `settings` text NOT NULL,
  `last_log` text NOT NULL,
  `date_updated` varchar(32) NOT NULL,
  `date_created` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `module_id` (`module_id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=MyISAM;
-- --------------------------------------------------------
ALTER TABLE `lc_categories_info` ADD UNIQUE INDEX `category_info` (`category_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_countries` CHANGE COLUMN `postcode_required` `postcode_required` TINYINT(1) NOT NULL COMMENT 'Deprecated, use instead postcode_format' AFTER `postcode_format`;
-- --------------------------------------------------------
ALTER TABLE `lc_customers` ADD `notes` TEXT NOT NULL AFTER `newsletter`, ADD COLUMN `password_reset_token` VARCHAR(128) NOT NULL AFTER `notes`;
-- --------------------------------------------------------
ALTER TABLE `lc_delivery_statuses_info` ADD UNIQUE INDEX `delivery_status_info` (`delivery_status_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_geo_zones` ADD COLUMN `code` VARCHAR(32) NOT NULL AFTER `id`;
-- --------------------------------------------------------
ALTER TABLE `lc_languages` DROP INDEX `id`, ADD PRIMARY KEY (`id`);
-- --------------------------------------------------------
ALTER TABLE `lc_manufacturers_info` ADD UNIQUE INDEX `manufacturer_info` (`manufacturer_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_option_groups_info`	ADD UNIQUE INDEX `option_group_info` (`group_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_option_values_info` ADD UNIQUE INDEX `option_value_info` (`value_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items` CHANGE COLUMN `options` `options` VARCHAR(4096) NOT NULL AFTER `option_stock_combination`;
-- --------------------------------------------------------
ALTER TABLE `lc_order_statuses_info` ADD UNIQUE INDEX `order_status_info` (`order_status_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_pages_info` ADD UNIQUE INDEX `page_info` (`page_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_info` ADD UNIQUE INDEX `product_info` (`product_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_options` ADD UNIQUE INDEX `product_option` (`product_id`, `group_id`, `value_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_options_stock` ADD UNIQUE INDEX `product_option_stock` (`product_id`, `combination`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_prices` ADD UNIQUE INDEX `product_price` (`product_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_to_categories` ADD UNIQUE INDEX `mapping` (`product_id`, `category_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_product_groups_info` ADD UNIQUE INDEX `product_group_info` (`product_group_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_product_groups_values_info` ADD UNIQUE INDEX `product_group_value_info` (`product_group_value_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_quantity_units_info` ADD UNIQUE INDEX `quantity_unit_info` (`quantity_unit_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_sold_out_statuses_info` ADD UNIQUE INDEX `sold_out_status_info` (`sold_out_status_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_zones_to_geo_zones` ADD UNIQUE INDEX `region` (`geo_zone_id`, `country_code`, `zone_code`);
-- --------------------------------------------------------
DELETE FROM `lc_settings` where `key` IN ('order_action_modules', 'order_success_modules');
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('', 'local', 'Installed Order Modules', '', 'order_modules', '', '', 0, NOW(), NOW()),
('images', 'local', 'Category Images: Clipping Method', 'The clipping method used for scaled category thumbnails.', 'category_image_clipping', 'CROP', 'select("CROP","FIT","FIT_USE_WHITESPACING")', '11', NOW(), NOW()),
('images', 'local', 'Product Images: Trim Whitespace', 'Trim whitespace in generated thumbnail images.', 'product_image_trim', '0', 'toggle("y/n")', '33', NOW(), NOW()),
('listings', 'global', 'Maintenance Mode', 'Setting the store in maintenance mode will prevent users from browsing your site.', 'maintenance_mode', '0', 'toggle()', 2, NOW(), NOW()),
('listings', 'local', 'Also Purchased Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_also_purchased_products_num_items', '4', 'int()', 20, NOW(), NOW()),
('security', 'global', 'Bad URLs Access Detection', 'Detect access to commonly attacked URLs.', 'security_bad_urls', '1', 'toggle("e/d")', 14, NOW(), NOW());
-- --------------------------------------------------------
INSERT IGNORE INTO `lc_countries` (`status`, `name`, `domestic_name`, `iso_code_1`, `iso_code_2`, `iso_code_3`, `tax_id_format`, `address_format`, `postcode_format`, `postcode_required`, `language_code`, `currency_code`, `phone_code`, `date_updated`, `date_created`) VALUES
(1, 'Guernsey', '', '831', 'GG', 'GGY', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', '', '44', NOW(), NOW()),
(1, 'Montenegro', '', '499', 'ME', 'MNE', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', '', '382', NOW(), NOW()),
(1, 'Jersey', '', '832', 'JE', 'JEY', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', '', '44', NOW(), NOW()),
(1, 'Isle of Man', '', '833', 'IM', 'IMN', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', '', '44', NOW(), NOW()),
(1, 'Åland Islands', '', '248', 'AX', 'ALA', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', 'EUR', '358', NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_suppliers` ADD COLUMN `code` VARCHAR(64) NOT NULL AFTER `id`,	ADD INDEX `code` (`code`);
-- --------------------------------------------------------
ALTER TABLE `lc_users` ADD COLUMN `permissions` VARCHAR(4096) NOT NULL AFTER `password`;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `title` = 'Popular Products Box: Number of Items', `key` = 'box_popular_products_num_items' WHERE `key` = 'box_most_popular_products_num_items';