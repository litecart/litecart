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
CREATE TABLE IF NOT EXISTS `lc_slides_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slide_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `caption` TEXT NOT NULL,
  `link` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slide_info` (`slide_id`,`language_code`),
  KEY `slide_id` (`slide_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM;
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('', 'local', 'Installed Order Modules', '', 'order_modules', '', '', 0, NOW(), NOW()),
('images', 'local', 'Category Images: Clipping Method', 'The clipping method used for scaled category thumbnails.', 'category_image_clipping', 'CROP', 'select("CROP","FIT","FIT_USE_WHITESPACING")', '11', NOW(), NOW()),
('images', 'local', 'Interlaced Thumbnails', 'Generate interlaced thumbnail images for progressive loading. Increases the filesize by 10-20% but improves user experience.', 'image_thumbnail_interlaced', '1', 'toggle()', '42', NOW(), NOW()),
('images', 'local', 'Product Images: Trim Whitespace', 'Trim whitespace before generating thumbnail images.', 'product_image_trim', '0', 'toggle("y/n")', '33', NOW(), NOW()),
('listings', 'global', 'Maintenance Mode', 'Setting the store in maintenance mode will prevent users from browsing your site.', 'maintenance_mode', '0', 'toggle()', '2', NOW(), NOW()),
('listings', 'local', 'Also Purchased Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_also_purchased_products_num_items', '4', 'int()', '20', NOW(), NOW()),
('security', 'global', 'Bad URLs Access Detection', 'Detect access to commonly attacked URLs.', 'security_bad_urls', '1', 'toggle("e/d")', '14', NOW(), NOW());
-- --------------------------------------------------------
INSERT IGNORE INTO `lc_countries` (`status`, `name`, `domestic_name`, `iso_code_1`, `iso_code_2`, `iso_code_3`, `tax_id_format`, `address_format`, `postcode_format`, `postcode_required`, `language_code`, `currency_code`, `phone_code`, `date_updated`, `date_created`) VALUES
(1, 'Guernsey', '', '831', 'GG', 'GGY', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', '', '44', NOW(), NOW()),
(1, 'Montenegro', '', '499', 'ME', 'MNE', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', '', '382', NOW(), NOW()),
(1, 'Jersey', '', '832', 'JE', 'JEY', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', '', '44', NOW(), NOW()),
(1, 'Isle of Man', '', '833', 'IM', 'IMN', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', '', '44', NOW(), NOW()),
(1, 'Åland Islands', '', '248', 'AX', 'ALA', '', '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', '', 0, 'en', 'EUR', '358', NOW(), NOW());
-- --------------------------------------------------------
INSERT IGNORE INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('', 'global', 'Catalog Template Settings', '', 'store_template_catalog_settings', '{"product_modal_window":"1","sidebar_parallax_effect":"1","cookie_acceptance":"1"}', 'smalltext()', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_categories_info` ADD UNIQUE INDEX `category_info` (`category_id`, `language_code`);
-- --------------------------------------------------------
ALTER TABLE `lc_countries` CHANGE COLUMN `postcode_required` `postcode_required` TINYINT(1) NOT NULL COMMENT 'Deprecated, use instead postcode_format' AFTER `postcode_format`;
-- --------------------------------------------------------
ALTER TABLE `lc_customers` ADD `notes` TEXT NOT NULL AFTER `newsletter`, ADD COLUMN `password_reset_token` VARCHAR(128) NOT NULL AFTER `notes`, CHANGE COLUMN `mobile` `shipping_phone` VARCHAR(24) NOT NULL AFTER `shipping_zone_code`;
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
ALTER TABLE `lc_orders` CHANGE COLUMN `customer_mobile` `shipping_phone` VARCHAR(24) NOT NULL AFTER `shipping_zone_code`;
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items` CHANGE COLUMN `options` `options` VARCHAR(4096) NOT NULL AFTER `option_stock_combination`;
-- --------------------------------------------------------
ALTER TABLE `lc_order_statuses_info` ADD COLUMN `email_subject` VARCHAR(128) NOT NULL AFTER `description`, ADD UNIQUE INDEX `order_status_info` (`order_status_id`, `language_code`);
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
ALTER TABLE `lc_suppliers` ADD COLUMN `code` VARCHAR(64) NOT NULL AFTER `id`,	ADD INDEX `code` (`code`);
-- --------------------------------------------------------
ALTER TABLE `lc_users` ADD COLUMN `permissions` VARCHAR(4096) NOT NULL AFTER `password`;
-- --------------------------------------------------------
ALTER TABLE `lc_zones_to_geo_zones` ADD UNIQUE INDEX `region` (`geo_zone_id`, `country_code`, `zone_code`);
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(AT)?U[0-9]{8}$' WHERE iso_code_2 = 'AT' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(BE)?0[0-9]{9}$' WHERE iso_code_2 = 'BE' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(BG)?[0-9]{9,10}$' WHERE iso_code_2 = 'BG' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(CY)?[0-9]{8}L$' WHERE iso_code_2 = 'CY' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(CZ)?[0-9]{8,10}$' WHERE iso_code_2 = 'CZ' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(DE)?[0-9]{9}$' WHERE iso_code_2 = 'DE' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(DK)?[0-9]{8}$' WHERE iso_code_2 = 'DK' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(EE)?[0-9]{9}$' WHERE iso_code_2 = 'EE' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(ES)?[0-9A-Z][0-9]{7}[0-9A-Z]$' WHERE iso_code_2 = 'ES' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(FI)?[0-9]{8}$' WHERE iso_code_2 = 'FI' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(FR)?[0-9A-Z]{2}[0-9]{9}$' WHERE iso_code_2 = 'FR' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(GB)?([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})$' WHERE iso_code_2 = 'GB' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(EL|GR)?[0-9]{9}$' WHERE iso_code_2 = 'GR' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(HR)?[0-9]{11}$' WHERE iso_code_2 = 'HR' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(HU)?[0-9]{8}$' WHERE iso_code_2 = 'HU' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(IE)?[0-9]S[0-9]{5}L$' WHERE iso_code_2 = 'IE' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(IT)?[0-9]{11}$' WHERE iso_code_2 = 'IT' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(LT)?([0-9]{9}|[0-9]{12})$' WHERE iso_code_2 = 'LT' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(LU)?[0-9]{8}$' WHERE iso_code_2 = 'LU' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(LV)?[0-9]{11}$' WHERE iso_code_2 = 'LV' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(MT)?[0-9]{8}$' WHERE iso_code_2 = 'MT' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(NL)?[0-9]{9}B[0-9]{2}$' WHERE iso_code_2 = 'NL' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(PL)?[0-9]{10}$' WHERE iso_code_2 = 'PL' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(PT)?[0-9]{9}$' WHERE iso_code_2 = 'PT' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(RO)?[0-9]{2,10}$' WHERE iso_code_2 = 'RO' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(SE)?(16|19|20)?[0-9]{6}-?[0-9]{4}(01)?$' WHERE iso_code_2 = 'SE' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(SI)?[0-9]{8}$' WHERE iso_code_2 = 'SI' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(SK)?[0-9]{10}$' WHERE iso_code_2 = 'SK' AND tax_id_format != '';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `title` = 'Popular Products Box: Number of Items', `key` = 'box_popular_products_num_items' WHERE `key` = 'box_most_popular_products_num_items';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = '6' WHERE `key` = 'box_recently_viewed_products_num_items';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 'default.catalog' WHERE `key` = 'store_template_catalog';
-- --------------------------------------------------------
DELETE FROM `lc_settings` where `key` IN ('cookie_acceptance', 'fields_customer_password', 'order_action_modules', 'order_success_modules');