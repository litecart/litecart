CREATE TABLE `lc_banners` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `languages` VARCHAR(64) NOT NULL DEFAULT '',
  `html` TEXT NOT NULL DEFAULT '',
  `image` VARCHAR(64) NOT NULL DEFAULT '',
  `link` VARCHAR(255) NOT NULL DEFAULT '',
  `keywords` VARCHAR(255) NOT NULL DEFAULT '',
  `total_views` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `total_clicks` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `date_valid_from` TIMESTAMP NULL DEFAULT NULL,
  `date_valid_to` TIMESTAMP NULL DEFAULT NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
-- -----
CREATE TABLE `lc_customers_addresses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) UNSIGNED NULL,
  `type` ENUM('','business','individual') NOT NULL DEFAULT '',
  `tax_id` VARCHAR(32) NOT NULL DEFAULT '',
  `company` VARCHAR(64) NOT NULL DEFAULT '',
  `firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `address1` VARCHAR(64) NOT NULL DEFAULT '',
  `address2` VARCHAR(64) NOT NULL DEFAULT '',
  `postcode` VARCHAR(8) NOT NULL DEFAULT '',
  `city` VARCHAR(32) NOT NULL DEFAULT '',
  `country_code` CHAR(2) NOT NULL DEFAULT '',
  `zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `phone` VARCHAR(24) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `customer_id` (`customer_id`) USING BTREE
) ENGINE=InnoDB;
-- -----
CREATE TABLE `lc_products_references` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `source_type` VARCHAR(32) NOT NULL DEFAULT '',
  `source` VARCHAR(32) NOT NULL DEFAULT '',
  `type` VARCHAR(32) NOT NULL DEFAULT '',
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `product_id` (`product_id`),
  INDEX `type` (`type`),
  INDEX `source` (`source`),
  INDEX `source_type` (`source_type`),
  UNIQUE INDEX `code` (`product_id`, `code`, `type`, `source`, `source_type`)
) ENGINE=InnoDB;
-- -----
CREATE TABLE `lc_stock_transactions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `description` MEDIUMTEXT NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
-- -----
CREATE TABLE `lc_stock_transactions_contents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `sku` varchar(32) UNSIGNED NOT NULL DEFAULT '0',
  `quantity_adjustment` FLOAT(11,4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
-- -----
RENAME TABLE `lc_cart_items` TO `lc_shopping_carts_items`;
-- -----
RENAME TABLE `lc_manufacturers` TO `lc_brands`;
-- -----
UPDATE `lc_brands` SET image = REPLACE(image, 'manufacturers/', 'brands/');
-- -----
RENAME TABLE `lc_manufacturers_info` TO `lc_brands_info`;
-- -----
RENAME TABLE `lc_products_options` TO `lc_products_customizations`;
-- -----
RENAME TABLE `lc_products_options_values` TO `lc_products_customizations_values`;
-- -----
ALTER TABLE `lc_attribute_groups_info`
CHANGE COLUMN `group_id` `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;
-- -----
RENAME TABLE `lc_users` TO `lc_administrators`;
-- -----
ALTER TABLE `lc_attribute_groups_info`
CHANGE COLUMN `group_id` `group_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `group_id`;
-- -----
ALTER TABLE `lc_attribute_values_info`
CHANGE COLUMN `value_id` `value_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `value_id`;
-- -----
ALTER TABLE `lc_brands_info`
CHANGE COLUMN `manufacturer_id` `brand_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `brand_id`,
ADD INDEX `brand_id` (`brand_id`);
-- -----
ALTER TABLE `lc_cart_items`
CHANGE COLUMN `customer_id` `customer_id` INT(11) UNSIGNED NULL AFTER `id`;
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `key`;
-- -----
ALTER TABLE `lc_categories`
CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`,
CHANGE COLUMN `parent_id` `parent_id` INT(11) UNSIGNED NULL AFTER `id`,
CHANGE COLUMN `google_taxonomy_id` `google_taxonomy_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `parent_id`;
-- -----
ALTER TABLE `lc_categories_filters`
CHANGE COLUMN `category_id` `category_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `attribute_group_id` `attribute_group_id` INT(11) UNSIGNED NOT NULL AFTER `category_id`;
-- ------
ALTER TABLE `lc_categories_info`
CHANGE COLUMN `category_id` `category_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `category_id`;
-- ------
ALTER TABLE `lc_categories_info`
CHANGE COLUMN `category_id` `category_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
ADD COLUMN `synonyms` VARCHAR(256) NOT NULL DEFAULT '' AFTER `description`,
ALTER TABLE `lc_categories_info`
ADD FULLTEXT INDEX `name` (`name`),
ADD FULLTEXT INDEX `short_description` (`short_description`),
ADD FULLTEXT INDEX `description` (`description`),
ADD FULLTEXT INDEX `synonyms` (`synonyms`);
-- ------
ALTER TABLE `lc_countries`
CHANGE COLUMN `postcode_format` `postcode_format` VARCHAR(255) NOT NULL DEFAULT '',
ADD UNIQUE INDEX `iso_code_1` (`iso_code_1`);
-- -----
ALTER TABLE `lc_currencies`
ADD INDEX `code` (`code`),
ADD INDEX `number` (`number`);
-- -----
ALTER TABLE `lc_customers`
ADD COLUMN `default_billing_address_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `shipping_phone`,
ADD COLUMN `default_shipping_address_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `default_billing_address_id`,
CHANGE COLUMN `tax_id` `billing_tax_id` VARCHAR(32) NOT NULL DEFAULT '' AFTER `password_hash`,
CHANGE COLUMN `company` `billing_company` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_tax_id`,
CHANGE COLUMN `firstname` `billing_firstname` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_company`,
CHANGE COLUMN `lastname` `billing_lastname` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_firstname`,
CHANGE COLUMN `address1` `billing_address1` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_lastname`,
CHANGE COLUMN `address2` `billing_address2` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_address1`,
CHANGE COLUMN `postcode` `billing_postcode` VARCHAR(8) NOT NULL DEFAULT '' AFTER `billing_address2`,
CHANGE COLUMN `city` `billing_city` VARCHAR(32) NOT NULL DEFAULT '' AFTER `billing_postcode`,
CHANGE COLUMN `country_code` `billing_country_code` VARCHAR(4) NOT NULL DEFAULT '' AFTER `billing_city`,
CHANGE COLUMN `zone_code` `billing_zone_code` VARCHAR(8) NOT NULL DEFAULT '' AFTER `billing_country_code`,
CHANGE COLUMN `phone` `billing_phone` VARCHAR(24) NOT NULL DEFAULT '' AFTER `billing_zone_code`;
CHANGE COLUMN `shipping_country_code` `shipping_country_code` CHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `last_ip` `last_ip_address` VARCHAR(39) NOT NULL DEFAULT '',
CHANGE COLUMN `last_host` `last_hostname` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `last_agent` `last_user_agent` VARCHAR(255) NOT NULL DEFAULT '';
-- -----
ALTER TABLE `lc_delivery_statuses_info`
CHANGE COLUMN `delivery_status_id` `delivery_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `id`;
-- -----
ALTER TABLE `lc_emails`
ADD COLUMN `ip_address` VARCHAR(39) NOT NULL DEFAULT '' AFTER `multiparts`,
ADD COLUMN `hostname` VARCHAR(128) NOT NULL DEFAULT '' AFTER `ip_address`,
ADD COLUMN `user_agent` VARCHAR(256) NOT NULL DEFAULT '' AFTER `hostname`,
ADD INDEX `status` (`status`);
-- -----
ALTER TABLE `lc_languages`
ADD INDEX `code` (`code`),
ADD INDEX `code2` (`code2`);
-- -----
ALTER TABLE `lc_newsletter_recipients`
CHANGE COLUMN `client_ip` `ip_address` VARCHAR(39) NOT NULL DEFAULT '';
-- -----
ALTER TABLE `lc_orders`
CHANGE COLUMN `order_status_id` `order_status_id` INT(11) NULL AFTER `unread`,
CHANGE COLUMN `customer_id` `customer_id` INT(11) NULL AFTER `order_status_id`,
CHANGE COLUMN `customer_email` `billing_email` VARCHAR(128) NOT NULL DEFAULT '' AFTER `customer_phone`,
CHANGE COLUMN `customer_tax_id` `billing_tax_id` VARCHAR(32) NOT NULL DEFAULT '' AFTER `customer_email`,
CHANGE COLUMN `customer_company` `billing_company` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_tax_id`,
CHANGE COLUMN `customer_firstname` `billing_firstname` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_company`,
CHANGE COLUMN `customer_lastname` `billing_lastname` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_firstname`,
CHANGE COLUMN `customer_address1` `billing_address1` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_lastname`,
CHANGE COLUMN `customer_address2` `billing_address2` VARCHAR(64) NOT NULL DEFAULT '' AFTER `billing_address1`,
CHANGE COLUMN `customer_city` `billing_city` VARCHAR(32) NOT NULL DEFAULT '' AFTER `billing_address2`,
CHANGE COLUMN `customer_postcode` `billing_postcode` VARCHAR(8) NOT NULL DEFAULT '' AFTER `billing_city`,
CHANGE COLUMN `customer_country_code` `billing_country_code` VARCHAR(2) NOT NULL DEFAULT '' AFTER `billing_postcode`,
CHANGE COLUMN `customer_zone_code` `billing_zone_code` VARCHAR(8) NOT NULL DEFAULT '' AFTER `billing_country_code`,
CHANGE COLUMN `customer_phone` `billing_phone` VARCHAR(24) NOT NULL DEFAULT '' AFTER `billing_zone_code`;
CHANGE COLUMN `shipping_country_code` `shipping_country_code` CHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `language_code` `language_code` NULL AFTER `reference`,
CHANGE COLUMN `payment_due` `total` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `tax_total` `total_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `client_ip` `ip_address` VARCHAR(39) NOT NULL DEFAULT '' COLLATE 'utf8mb4_swedish_ci' AFTER `affiliate_id`,
ADD COLUMN `no` VARCHAR(16) NOT NULL DEFAULT '' AFTER `id`,
ADD COLUMN `shipping_tax_id` VARCHAR(128) NOT NULL DEFAULT '' AFTER `billing_email`,
ADD COLUMN `shipping_email` VARCHAR(128) NOT NULL DEFAULT '' AFTER `shipping_phone`,
ADD COLUMN `shipping_option_userdata` VARCHAR(512) NOT NULL DEFAULT '' AFTER `shipping_option_name`,
ADD COLUMN `shipping_option_fee` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `shipping_option_userdata`,
ADD COLUMN `shipping_option_tax` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `shipping_option_fee`,
ADD COLUMN `shipping_purchase_cost` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `shipping_option_tax`,
ADD COLUMN `shipping_progress` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `shipping_tracking_url`,
ADD COLUMN `shipping_current_status` VARCHAR(64) NOT NULL DEFAULT '' AFTER `shipping_progress`,
ADD COLUMN `shipping_current_location` VARCHAR(128) NOT NULL DEFAULT '' AFTER `shipping_current_status`,
ADD COLUMN `payment_option_userdata` VARCHAR(512) NOT NULL DEFAULT '' AFTER `payment_option_name`,
ADD COLUMN `payment_option_fee` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `payment_option_userdata`,
ADD COLUMN `payment_option_tax` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `payment_option_fee`,
ADD COLUMN `payment_transaction_fee` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `payment_transaction_id`,
ADD COLUMN `subtotal` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `display_prices_including_tax`,
ADD COLUMN `subtotal_tax` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `subtotal`,
ADD COLUMN `discount` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `subtotal_tax`,
ADD COLUMN `discount_tax` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `discount`,
ADD COLUMN `notes` VARCHAR(1024) NOT NULL DEFAULT '' AFTER `total_tax`,
ADD COLUMN `hostname` VARCHAR(128) NOT NULL DEFAULT '' AFTER `ip_address`,
ADD INDEX `no` (`no`);
-- -----
ALTER TABLE `lc_orders_comments`
CHANGE COLUMN `order_id` `order_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;
-- -----
ALTER TABLE `lc_orders_items`
CHANGE COLUMN `order_id` `order_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`,
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NULL AFTER `order_id`,
CHANGE COLUMN `data` `userdata` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_y` `width` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_z` `height` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `options` `configuration` VARCHAR(1024) NOT NULL DEFAULT '',
CHANGE COLUMN `option_stock_combination` `attributes` VARCHAR(32) NOT NULL DEFAULT '',
ADD COLUMN `discount` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `tax`,
ADD COLUMN `discount_tax` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `discount`,
ADD COLUMN `sum` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `discount_tax`,
ADD COLUMN `sum_tax` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `sum`,
ADD COLUMN `downloads` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `length_unit`,
ADD COLUMN `priority` INT NOT NULL DEFAULT '0' AFTER `downloads`,
ADD INDEX `product_id` (`product_id`),
ADD INDEX `stock_option_id` (`stock_option_id`);
-- -----
ALTER TABLE `lc_orders_totals`
CHANGE COLUMN `order_id` `order_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`,
CHANGE COLUMN `value` `amount` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `title`,
ADD COLUMN `discount` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `tax`;
-- -----
ALTER TABLE `lc_order_statuses`
ADD COLUMN `hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;
-- -----
ALTER TABLE `lc_order_statuses_info`
CHANGE COLUMN `order_status_id` `order_status_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `order_status_id`;
-- -----
ALTER TABLE `lc_pages`
CHANGE COLUMN `parent_id` `parent_id` INT(11) UNSIGNED NULL AFTER `id`;
-- -----
ALTER TABLE `lc_pages_info`
CHANGE COLUMN `page_id` `page_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `page_id`;
-- -----
ALTER TABLE `lc_products`
ADD COLUMN `type` ENUM('virtual','physical','digital','variable','bundle') NOT NULL DEFAULT 'virtual' AFTER `id`,
ADD COLUMN `synonyms` VARCHAR(256) NOT NULL DEFAULT '' AFTER `keywords`,
ADD COLUMN `autofill_technical_data` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `image`,
CHANGE COLUMN `keywords` `keywords` VARCHAR(256) NOT NULL DEFAULT '' AFTER `taric`,
CHANGE COLUMN `manufacturer_id` `brand_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_y` `width` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_z` `height` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `quantity_min` `quantity_min` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '1.0000' AFTER `quantity`,
ADD COLUMN `file` VARCHAR(128) NOT NULL DEFAULT '' AFTER `image`,
ADD COLUMN `filename` VARCHAR(128) NOT NULL DEFAULT '' AFTER `file`,
ADD COLUMN `mime_type` VARCHAR(32) NOT NULL DEFAULT '' AFTER `filename`,
DROP INDEX `manufacturer_id`,
ADD INDEX `type` (`type`),
ADD INDEX `brand_id` (`brand_id`),
ADD INDEX `synonyms` (`synonyms`);
-- -----
ALTER TABLE `lc_products_attributes`
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `group_id` `group_id` INT(11) UNSIGNED NOT NULL AFTER `product_id`,
CHANGE COLUMN `value_id` `value_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `group_id`,
ADD COLUMN `priority` INT NOT NULL DEFAULT '0' AFTER `custom_value`;
-- -----
ALTER TABLE `lc_products_campaigns`
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL AFTER `id`;
-- -----
ALTER TABLE `lc_products_images`
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `checksum` `checksum` CHAR(32) NOT NULL DEFAULT '';
-- -----
ALTER TABLE `lc_products_info`
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `product_id`;
-- -----
ALTER TABLE `lc_products_prices`
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL AFTER `id`;
-- -----
ALTER TABLE `lc_products_stock_options`
CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL,
CHANGE COLUMN `combination` `attributes` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `sku` `sku` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_y` `width` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_z` `height` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
ADD COLUMN `purchase_price` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `length_unit`,
ADD COLUMN `backordered` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `quantity`,
ADD UNIQUE KEY `product_stock_option` (`product_id`, `attributes`),
ADD INDEX `sku` (`sku`),
ADD INDEX `gtin` (`gtin`),
ADD INDEX `mpn` (`mpn`);
-- -----
ALTER TABLE `lc_products_to_categories`
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL FIRST,
CHANGE COLUMN `category_id` `category_id` INT(11) UNSIGNED NOT NULL COMMENT 'test' AFTER `product_id`;
-- -----
ALTER TABLE `lc_quantity_units_info`
CHANGE COLUMN `quantity_unit_id` `quantity_unit_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `quantity_unit_id`;
-- -----
ALTER TABLE `lc_settings`
ADD COLUMN `required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `function`,
CHANGE COLUMN `setting_group_key` `group_key` VARCHAR(32) NULL,
CHANGE COLUMN `key` `key` VARCHAR(32) NULL DEFAULT NULL DEFAULT '',
CHANGE COLUMN `description` `description` VARCHAR(255) NOT NULL DEFAULT '',
CHANGE COLUMN `value` `value` VARCHAR(255) NOT NULL DEFAULT '',
ADD INDEX `type` (`type`),
ADD INDEX `group_key` (`group_key`),
DROP INDEX `setting_group_key`;
-- -----
ALTER TABLE `lc_settings_groups`
CHANGE COLUMN `key` `key` VARCHAR(32) NOT NULL;
-- -----
ALTER TABLE `lc_slides_info`
CHANGE COLUMN `slide_id` `slide_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;
-- -----
ALTER TABLE `lc_sold_out_statuses_info`
CHANGE COLUMN `sold_out_status_id` `sold_out_status_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL AFTER `sold_out_status_id`;
-- -----
ALTER TABLE `lc_tax_rates`
CHANGE COLUMN `tax_class_id` `tax_class_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `geo_zone_id` `geo_zone_id` INT(11) UNSIGNED NOT NULL AFTER `tax_class_id`,
CHANGE COLUMN `rate` `rate` FLOAT(4,2) NOT NULL DEFAULT '0' AFTER `description`,
DROP COLUMN `type`;
-- -----
ALTER TABLE `lc_translations`
CHANGE COLUMN `code` `code` VARCHAR(128) NOT NULL DEFAULT '';
-- -----
ALTER TABLE `lc_users`
CHANGE COLUMN `last_ip` `last_ip_address` VARCHAR(39) NOT NULL DEFAULT '',
CHANGE COLUMN `last_host` `last_hostname` VARCHAR(64) NOT NULL DEFAULT '',
ADD COLUMN `last_user_agent` VARCHAR(255) NOT NULL DEFAULT '' AFTER `last_hostname`;
-- -----
ALTER TABLE `lc_zones`
CHANGE COLUMN `country_code` `country_code` CHAR(2) NOT NULL;
CHANGE COLUMN `code` `code` VARCHAR(8) NOT NULL;
-- -----
ALTER TABLE `lc_zones_to_geo_zones`
CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
CHANGE COLUMN `geo_zone_id` `geo_zone_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
CHANGE COLUMN `country_code` `country_code` CHAR(2) NOT NULL AFTER `geo_zone_id`,
CHANGE COLUMN `zone_code` `zone_code` VARCHAR(8) NULL AFTER `country_code`;
CHANGE COLUMN `city` `city` VARCHAR(32) NULL AFTER `zone_code`;
-- -----
INSERT IGNORE INTO `lc_banners`
(`id`, `status`, `name`, `languages`, `html`, `image`, `link`, `keywords`, `date_valid_from`, `date_valid_to`)
SELECT id, status, name, languages, '', replace(image, 'slides/', 'banners/'), '', 'leaderboard', date_valid_from, date_valid_to FROM `lc_slides`;
-- -----
INSERT INTO `lc_banners` (`status`, `name`, `languages`, `html`, `image`, `link`, `keywords`, `total_views`, `total_clicks`, `date_valid_from`, `date_valid_to`, `date_updated`, `date_created`) VALUES
(0, 'Left', '', '<div class="placeholder" data-aspect-ratio="2:1" style="background: ivory;">Left</div>', '', '', 'left', 0, 0, NULL, NULL, NOW(), NOW()),
(0, 'Middle', '', '<div class="placeholder" data-aspect-ratio="2:1" style="background: ivory;">Middle</div>', '', '', 'middle', 0, 0, NULL, NULL, NOW(), NOW()),
(0, 'Right', '', '<div class="placeholder" data-aspect-ratio="2:1" style="background: seashell;">Right</div>', '', '', 'right', 0, 0, NULL, NULL, NOW(), NOW());
-- -----
INSERT IGNORE INTO `lc_customers_addresses`
(customer_id, tax_id, company, firstname, lastname, address1, address2, postcode, city, country_code, zone_code, phone)
SELECT DISTINCT id, billing_tax_id, billing_company, billing_firstname, billing_lastname, billing_address1, billing_address2, billing_postcode, billing_city, billing_country_code, billing_zone_code, billing_phone
FROM `lc_customers`
ORDER BY id ASC;
-- -----
INSERT IGNORE INTO `lc_customers_addresses`
(customer_id, tax_id, company, firstname, lastname, address1, address2, postcode, city, country_code, zone_code, phone)
SELECT DISTINCT id, shipping_tax_id, shipping_company, shipping_firstname, shipping_lastname, shipping_address1, shipping_address2, shipping_postcode, shipping_city, shipping_country_code, shipping_zone_code, shipping_phone
FROM `lc_customers`
WHERE different_shipping_address = 1
ORDER BY id ASC;
-- -----
INSERT INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES
('social_media', 'Social Media', 'Social media related settings.', 30);
-- -----
INSERT INTO `lc_settings` (`group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `required`, `priority`, `date_updated`, `date_created`) VALUES
('defaults', 'local', 'Default Incoterm', 'Default Incoterm for new orders if nothing else is set.', 'default_incoterm', 'EXW', 'incoterms()', 0, 19, NOW(), NOW()),
('defaults', 'local', 'Default Order Status', 'Default order status for new orders if nothing else is set.', 'default_order_status_id', '1', 'order_status()', 0, 20, NOW(), NOW()),
('customer_details', 'local', 'Different Shipping Address', 'Allow customers to provide a different address for shipping.', 'customer_shipping_address', '1', 'toggle("y/n")', 0, 24, NOW(), NOW()),
('checkout', 'local', 'Order Number Format', 'Specify the format for creating order numbers. {id} = order id,  {yy} = year, {mm} = month, {q} = quarter, {l} length digit, {#} = luhn checksum digit', 'order_no_format', '{id}', 'text()', 1, 20, NOW(), NOW()),
('advanced', 'global', 'Static Content Domain Name', 'Use the given alias domain name for static content (fonts, images, stylesheets, javascripts, etc.).', 'static_domain', '', 'text()', 0, 12, NOW(), NOW()),
('social_media', 'global', 'Facebook Link', 'The link to your Facebook page.', 'facebook_link', '', 'url()', 0, 10, NOW(), NOW()),
('social_media', 'global', 'Instagram Link', 'The link to your Instagram page.', 'instagram_link', '', 'url()', 0, 20, NOW(), NOW()),
('social_media', 'global', 'LinkedIn Link', 'The link to your LinkedIn page.', 'linkedin_link', '', 'url()', 0, 30, NOW(), NOW()),
('social_media', 'global', 'Pinterest Link', 'The link to your Pinterest page.', 'pinterest_link', '', 'url()', 0, 40, NOW(), NOW()),
('social_media', 'global', 'Twitter Link', 'The link to your Twitter page.', 'twitter_link', '', 'url()', 0, 50, NOW(), NOW()),
('social_media', 'global', 'YouTube Link', 'The link to your YouTube channel.', 'youtube_link', '', 'url()', 0, 60, NOW(), NOW());
-- -----
INSERT INTO `lc_stock_transactions` (id, name, description)
VALUES (1, 'Initial Stock Transaction', 'This is an initial system generated stock transaction to deposit stock for all sold items and items in stock. We need this for future inconcistency checks.');
-- -----
INSERT INTO `lc_stock_transactions_contents`
(transaction_id, product_id, stock_option_id, quantity_adjustment)
SELECT '1' AS transaction_id, product_id, stock_option_id, quantity_adjustment FROM (
  SELECT product_id, stock_option_id, SUM(quantity) as quantity_adjustment FROM (

    SELECT pso.product_id, pso.id AS stock_option_id, pso.quantity
    FROM `lc_products_stock_options` pso

    UNION SELECT oi.product_id, oi.stock_option_id, oi.quantity FROM `lc_orders_items` oi
   WHERE oi.order_id IN (
      SELECT id FROM `lc_orders` o
      WHERE o.order_status_id IN (
        SELECT id FROM `lc_order_statuses` os
        WHERE os.stock_action = 'withdraw'
      )
    )

  )
  GROUP BY product_id, stock_option_id
  ORDER BY product_id, stock_option_id
);
-- -----
UPDATE `lc_cart_items` SET customer_id = NULL WHERE customer_id = 0;
-- -----
UPDATE `lc_categories` SET parent_id = NULL WHERE parent_id = 0;
-- -----
UPDATE `lc_orders` SET order_status_id = NULL WHERE order_status_id = 0;
-- -----
UPDATE `lc_orders` SET customer_id = NULL WHERE customer_id = 0;
-- -----
UPDATE `lc_orders` SET language_code = NULL WHERE language_code = '';
-- -----
UPDATE `lc_orders_items` SET product_id = NULL WHERE product_id = 0;
-- -----
UPDATE `lc_modules` SET `settings` = REPLACE(settings, 'weight_class', 'weight_unit') WHERE `module_id` = 'sm_zone_weight' LIMIT 1;
-- -----
UPDATE `lc_orders` SET `no` = id;
-- -----
UPDATE `lc_products` SET `type` = 'physical';
-- -----
UPDATE `lc_pages` SET `dock` = REPLACE('customer_service', 'information');
-- -----
UPDATE `lc_settings` SET `value` = '0' WHERE `key` = 'cache_clear_thumbnails' LIMIT 1;
-- -----
UPDATE `lc_settings` SET `key` = 'template', title = 'Template', `value` = REGEXP_REPLACE(`value`, '\.catalog$', '') WHERE `key` = 'store_template_catalog' LIMIT 1;
-- -----
UPDATE `lc_settings` SET `key` = 'template_settings', title = 'Template Settings' WHERE `key` = 'store_template_catalog_settings';
-- -----
UPDATE `lc_settings` SET `key` = 'store_weight_unit',  `title` = 'Store Weight Unit', `description` = 'The prefered weight unit.' WHERE `key` = 'store_length_class' LIMIT 1;
-- -----
UPDATE `lc_settings` SET `key` = 'store_length_unit', `title` = 'Store Length Unit', `description` = 'The prefered length unit.' WHERE `key` = 'store_weight_class' LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `required` = 1
WHERE `key` IN (
  'store_email', 'store_name', 'store_language_code','store_currency_code', 'store_weight_unit', 'store_length_unit', 'store_timezone',
  'default_language_code', 'default_currency_code', 'default_country_code', 'default_zone_code', 'template'
);
-- -----
UPDATE `lc_settings` SET `function` = 'select("FIT","CROP")' WHERE `key` = 'category_image_clipping' LIMIT 1;
-- -----
UPDATE `lc_settings` SET `value` = 'FIT' WHERE `key` = 'category_image_clipping' AND `value` IN ('FIT_USE_WHITESPACING', 'FIT_ONLY_BIGGER', 'FIT_ONLY_BIGGER_USE_WHITESPACING') LIMIT 1;
-- -----
UPDATE `lc_settings` SET `function` = 'select("FIT","CROP")' WHERE `key` = 'product_image_clipping' LIMIT 1;
-- -----
UPDATE `lc_settings` SET `value` = 'FIT' WHERE `key` = 'product_image_clipping' AND `value` IN ('FIT_USE_WHITESPACING', 'FIT_ONLY_BIGGER', 'FIT_ONLY_BIGGER_USE_WHITESPACING') LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `function` = 'regional_text()'
WHERE `function` = 'regional_input()';
-- -----
UPDATE `lc_orders`
SET shipping_tax_id = billing_tax_id,
  shipping_phone = billing_phone,
  shipping_email = billing_email;
-- -----
UPDATE `lc_orders` o
LEFT JOIN `lc_orders_totals` ot ON (ot.order_id = o.id AND ot.module_id = 'ot_subtotal')
SET o.subtotal = ot.`amount`,
o.subtotal_tax = ot.`tax`;
-- -----
UPDATE `lc_orders` o
LEFT JOIN (
  SELECT order_id, sum(`amount`) as discount, sum(`tax`) as discount_tax
  FROM `lc_orders_totals`
  WHERE `amount` < 0 AND calculate
  GROUP BY order_id
) ot ON (ot.order_id = o.id)
SET o.discount = 0 - if(ot.discount, ot.discount, 0),
o.discount_tax = 0 - if(ot.discount_tax, ot.discount_tax, 0);
-- -----
UPDATE `lc_orders_items` oi
LEFT JOIN `lc_orders` o ON (o.id = oi.invoice_id)
LEFT JOIN `lc_products` p ON (p.id = oi.product_id)
SET oi.tax_class_id = p.tax_class_id,
  oi.discount = oi.price * (o.discount/o.total),
  oi.discount_tax = oi.price * (o.discount_tax/o.total),
  oi.`sum` = oi.price - (oi.price * (o.discount/o.total)),
  oi.sum_tax = oi.tax - (oi.tax * (o.discount/o.total));
-- -----
UPDATE `lc_orders_items` oi
LEFT JOIN `lc_products_stock_options` pso ON (pso.product_id = oi.product_id AND pso.attributes = oi.attributes)
SET oi.stock_option_id = pso.id;
-- -----
UPDATE `lc_orders_items`
SET `type` = 'product',
  sum = price * quantity,
  sum_tax = tax * quantity;
-- -----
UPDATE `lc_settings`
SET `key` = 'jobs_last_push',
  `title` = 'Background Jobs Last Push',
  `description` = 'Time when background jobs were last pushed.'
WHERE `key` = 'jobs_last_run'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `value` = 'https://'
WHERE `value` IN ('?app=settings&doc=advanced&action=edit&key=control_panel_link', '?app=settings&doc=advanced&action=edit&key=database_admin_link', '?app=settings&doc=advanced&action=edit&key=webmail_link');
-- -----
UPDATE `lc_zones_to_geo_zones` SET `zone_code` = NULL WHERE `zone_code` = '';
-- -----
ALTER TABLE `lc_emails`
DROP COLUMN `charset`;
-- -----
ALTER TABLE `lc_languages`
DROP COLUMN `charset`;
-- -----
ALTER TABLE `lc_orders`
DROP COLUMN `uid`;
-- -----
ALTER TABLE `lc_products`
DROP COLUMN `upc`;
-- -----
ALTER TABLE `lc_products_stock_options`
DROP INDEX `product_option_stock`;
-- -----
ALTER TABLE `lc_categories`
DROP COLUMN `list_style`;
-- -----
ALTER TABLE `lc_orders_items`
DROP COLUMN `tax`;
-- -----
DELETE FROM `lc_orders_totals` WHERE module_id = 'ot_subtotal';
-- -----
DELETE FROM `lc_settings` WHERE `key` IN ('store_template_admin', 'store_template_admin_settings', 'round_amounts', 'cache_system_breakpoint', 'jobs_interval', 'jobs_last_push');
-- -----
DELETE FROM `lc_modules` WHERE `module_id` = 'ot_subtotal' LIMIT 1;
-- -----
/* Cleanup before foreign keys */
DELETE FROM `lc_attribute_groups_info` WHERE group_id NOT IN (SELECT id from `lc_attribute_groups`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_attribute_values` WHERE group_id NOT IN (SELECT id from `lc_attribute_groups`);
-- -----
DELETE FROM `lc_attribute_values_info` WHERE value_id NOT IN (SELECT id from `lc_attribute_values`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_brands_info` WHERE brand_id NOT IN (SELECT id from `lc_brands`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_cart_items` WHERE customer_id != 0 AND customer_id NOT IN (SELECT id from `lc_customers`);
-- -----
DELETE FROM `lc_cart_items` WHERE product_id NOT IN (SELECT id from `lc_products`);
-- -----
DELETE FROM `lc_categories_info` WHERE category_id NOT IN (SELECT code from `lc_categories`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_categories_images` WHERE category_id NOT IN (SELECT id from `lc_categories`);
-- -----
DELETE FROM `lc_categories_filters` WHERE category_id NOT IN (SELECT id from `lc_categories`) OR attribute_group_id NOT IN (SELECT id from `lc_attribute_groups`);
-- -----
DELETE FROM `lc_categories_info` WHERE category_id NOT IN (SELECT id from `lc_categories`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_delivery_statuses_info` WHERE delivery_status_id NOT IN (SELECT id from `lc_delivery_statuses`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_orders_comments` WHERE order_id NOT IN (SELECT id from `lc_orders`);
-- -----
DELETE FROM `lc_orders_items` WHERE order_id NOT IN (SELECT id from `lc_orders`);
-- -----
DELETE FROM `lc_orders_totals` WHERE order_id NOT IN (SELECT id from `lc_orders`);
-- -----
DELETE FROM `lc_order_statuses_info` WHERE order_status_id NOT IN (SELECT id from `lc_order_statuses`) OR  language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_pages_info` WHERE page_id NOT IN (SELECT id from `lc_pages`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_order_statuses_info` WHERE language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_products_attributes` WHERE product_id NOT IN (SELECT id from `lc_products`) OR group_id NOT IN (SELECT id from `lc_attribute_groups`);
-- -----
DELETE FROM `lc_products_campaigns` WHERE product_id NOT IN (SELECT id from `lc_products`);
-- -----
DELETE FROM `lc_products_images` WHERE product_id NOT IN (SELECT id from `lc_products`);
-- -----
DELETE FROM `lc_products_info` WHERE product_id NOT IN (SELECT id from `lc_products`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_products_to_categories` WHERE product_id NOT IN (SELECT id from `lc_products`) OR category_id NOT IN (SELECT id from `lc_categories`);
-- -----
DELETE FROM `lc_quantity_units_info` WHERE quantity_unit_id NOT IN (SELECT id from `lc_quantity_units`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_settings` WHERE `key` IN ('gzip_enabled');
-- -----
DELETE FROM `lc_slides_info` WHERE slide_id NOT IN (SELECT id from `lc_slides`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_sold_out_statuses_info` WHERE sold_out_status_id NOT IN (SELECT id from `lc_sold_out_statuses`) OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_tax_rates` WHERE tax_class_id NOT IN (SELECT id from `lc_tax_classes`) OR geo_zone_id NOT IN (SELECT id from `lc_geo_zones`);
-- -----
DELETE FROM `lc_zones` WHERE country_code NOT IN (SELECT iso_code_2 from `lc_countries`);
-- -----
DELETE FROM `lc_zones_to_geo_zones` WHERE geo_zone_id NOT IN (SELECT id from `lc_geo_zones`) OR country_code NOT IN (SELECT iso_code_2 from `lc_countries`);
-- -----
/* Add foreign keys */
ALTER TABLE `lc_attribute_groups_info`
ADD CONSTRAINT `attribute_group_info_to_attribute_group` FOREIGN KEY (`group_id`) REFERENCES `lc_attribute_groups` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `attribute_group_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE ;
-- -----
ALTER TABLE `lc_attribute_values_info`
ADD CONSTRAINT `attribute_value_info to attribute_value` FOREIGN KEY (`value_id`) REFERENCES `lc_attribute_values` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `attribute_value_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_brands_info`
ADD CONSTRAINT `brand` FOREIGN KEY (`brand_id`) REFERENCES `lc_brands` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `brand_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_cart_items`
ADD CONSTRAINT `cart_item_to_customer` FOREIGN KEY (`customer_id`) REFERENCES `lc_customers` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `cart_item_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_categories`
ADD CONSTRAINT `category_to_parent` FOREIGN KEY (`parent_id`) REFERENCES `lc_categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_categories_filters`
ADD CONSTRAINT `category_filter_to_category` FOREIGN KEY (`category_id`) REFERENCES `lc_categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `category_filter_to_attribute_group` FOREIGN KEY (`attribute_group_id`) REFERENCES `lc_attribute_groups` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_categories_images`
ADD CONSTRAINT `category_image_to_category` FOREIGN KEY (`category_id`) REFERENCES `lc_categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_categories_info`
ADD CONSTRAINT `category_info_to_category` FOREIGN KEY (`category_id`) REFERENCES `lc_categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `category_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_delivery_statuses_info`
ADD CONSTRAINT `delivery_status_info_to_delivery_status` FOREIGN KEY (`delivery_status_id`) REFERENCES `lc_delivery_statuses` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `delivery_status_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_orders`
ADD CONSTRAINT `order_to_customer` FOREIGN KEY (`customer_id`) REFERENCES `lc_customers` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
ADD CONSTRAINT `order_to_order_status` FOREIGN KEY (`order_status_id`) REFERENCES `lc_order_statuses` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;
-- -----
ALTER TABLE `lc_orders_comments`
ADD CONSTRAINT `order_comment_to_order` FOREIGN KEY (`order_id`) REFERENCES `lc_orders` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_orders_items`
ADD CONSTRAINT `order_item_to_order` FOREIGN KEY (`order_id`) REFERENCES `lc_orders` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_orders_totals`
ADD CONSTRAINT `order_total_to_order` FOREIGN KEY (`order_id`) REFERENCES `lc_orders` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_order_statuses_info`
ADD CONSTRAINT `order_status_info_to_order` FOREIGN KEY (`order_status_id`) REFERENCES `lc_order_statuses` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `order_status_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_pages_info`
ADD CONSTRAINT `page_info_to_page` FOREIGN KEY (`page_id`) REFERENCES `lc_pages` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `page_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_products_attributes`
ADD CONSTRAINT `product_attribute_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `product_attribute_to_attribute_group` FOREIGN KEY (`group_id`) REFERENCES `lc_attribute_groups` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `product_attribute_to_attribute_value` FOREIGN KEY (`value_id`) REFERENCES `lc_attribute_values` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_products_campaigns`
ADD CONSTRAINT `product_campaign_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_products_images`
ADD CONSTRAINT `product_image_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_products_info`
ADD CONSTRAINT `product_info_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `product_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_products_images`
ADD CONSTRAINT `product_price_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_products_to_categories`
ADD CONSTRAINT `product_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `product_to_category` FOREIGN KEY (`category_id`) REFERENCES `lc_categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_quantity_units_info`
ADD CONSTRAINT `quantity_unit_info_to_quantity_unit` FOREIGN KEY (`quantity_unit_id`) REFERENCES `lc_quantity_units` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `quantity_unit_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_slides_info`
ADD CONSTRAINT `slide_info_to_slide` FOREIGN KEY (`slide_id`) REFERENCES `lc_slides` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `slide_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_sold_out_statuses_info`
ADD CONSTRAINT `sold_out_status_info_to_sold_out_status` FOREIGN KEY (`sold_out_status_id`) REFERENCES `lc_sold_out_statuses` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `sold_out_status_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_stock_transactions_contents`
ADD CONSTRAINT `stock_transaction_content_to_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `lc_stock_transactions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `stock_transaction_content_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION,
ADD CONSTRAINT `stock_transaction_content_to_stock_option` FOREIGN KEY (`sku`) REFERENCES `lc_products_options_stock` (`sku`) ON UPDATE CASCADE ON DELETE NO ACTION;
-- -----
ALTER TABLE `lc_tax_rates`
ADD CONSTRAINT `tax_rate_to_tax_class` FOREIGN KEY (`tax_class_id`) REFERENCES `lc_tax_classes` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `tax_rate_to_geo_zone` FOREIGN KEY (`geo_zone_id`) REFERENCES `lc_geo_zones` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_zones`
ADD CONSTRAINT `zone_to_country` FOREIGN KEY (`country_code`) REFERENCES `lc_countries` (`iso_code_2`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_zones_to_geo_zones`
ADD CONSTRAINT `zone_entry_to_geo_zone` FOREIGN KEY (`geo_zone_id`) REFERENCES `lc_geo_zones` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT `zone_entry_to_country` FOREIGN KEY (`country_code`) REFERENCES `lc_countries` (`iso_code_2`) ON UPDATE CASCADE ON DELETE CASCADE;
-- -----
ALTER TABLE `lc_customers`
DROP COLUMN `different_shipping_address`,
DROP COLUMN `shipping_company`,
DROP COLUMN `shipping_firstname`,
DROP COLUMN `shipping_lastname`,
DROP COLUMN `shipping_address1`,
DROP COLUMN `shipping_address2`,
DROP COLUMN `shipping_city`,
DROP COLUMN `shipping_postcode`,
DROP COLUMN `shipping_country_code`,
DROP COLUMN `shipping_zone_code`,
DROP COLUMN `shipping_phone`;
-- -----
DROP TABLE `lc_slides`;
DROP TABLE `lc_slides_info`;
