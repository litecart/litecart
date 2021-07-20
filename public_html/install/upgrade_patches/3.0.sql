ALTER TABLE `lc_cart_items`
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `key`,
ADD COLUMN `stock_item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `product_id`;
-- --------------------------------------------------------
CREATE TABLE `lc_banners` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) NOT NULL DEFAULT '0',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`languages` VARCHAR(64) NOT NULL DEFAULT '',
	`html` TEXT NOT NULL DEFAULT '',
	`image` VARCHAR(64) NOT NULL DEFAULT '',
	`link` VARCHAR(256) NOT NULL DEFAULT '',
	`keywords` VARCHAR(256) NOT NULL DEFAULT '',
	`total_views` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`total_clicks` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`date_valid_from` TIMESTAMP NULL DEFAULT NULL,
	`date_valid_to` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
);
-- --------------------------------------------------------
ALTER TABLE `lc_categories`
DROP COLUMN `list_style`;
-- --------------------------------------------------------
ALTER TABLE `lc_customers`
CHANGE COLUMN `last_ip` `last_ip_address` VARCHAR(39) NOT NULL DEFAULT '' AFTER `num_logins`,
CHANGE COLUMN `last_host` `last_hostname` VARCHAR(64) NOT NULL DEFAULT '' AFTER `last_ip_address`,
CHANGE COLUMN `last_agent` `last_user_agent` VARCHAR(256) NOT NULL DEFAULT '' AFTER `last_hostname`;
-- --------------------------------------------------------
RENAME TABLE `lc_products_options` TO `lc_products_customizations`;
-- --------------------------------------------------------
RENAME TABLE `lc_products_options_values` TO `lc_products_customizations_values`;
-- --------------------------------------------------------
RENAME TABLE `lc_products_options_stock` TO `lc_products_stock_options`;
-- --------------------------------------------------------
ALTER TABLE `lc_products_stock_options`
ADD COLUMN `gtin` VARCHAR(64) NOT NULL DEFAULT '' AFTER `sku`,
ADD COLUMN `image` VARCHAR(128) NOT NULL DEFAULT '' AFTER `quantity`,
DROP COLUMN `date_updated`,
DROP COLUMN `date_created`,
DROP INDEX `product_option_stock`,
ADD UNIQUE INDEX `stock_option` (`product_id`, `combination`);
-- --------------------------------------------------------
ALTER TABLE `lc_orders`
ADD INDEX `uid` (`uid`);
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items`
ADD COLUMN `stock_item_id` INT(11) NOT NULL DEFAULT '0' AFTER `product_id`,
ADD COLUMN `description` VARCHAR(256) NOT NULL DEFAULT '' AFTER `name`,
CHANGE COLUMN `options` `data` VARCHAR(1024) NOT NULL DEFAULT '' AFTER `description`,
ADD COLUMN `priority` INT NOT NULL DEFAULT 0 AFTER `length_unit`,
ADD INDEX `product_id` (`product_id`),
ADD INDEX `stock_item_id` (`stock_item_id`);
-- --------------------------------------------------------
UPDATE `lc_orders_items` oi
LEFT JOIN `lc_products_stock_options` pso ON (pso.product_id = oi.product_id AND pso.combination = oi.option_stock_combination)
SET stock_item_id = pso.id;
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items`
DROP COLUMN `option_stock_combination`;
-- --------------------------------------------------------
ALTER TABLE `lc_order_statuses`
DROP COLUMN `keywords`;
-- --------------------------------------------------------
RENAME TABLE `lc_manufacturers` TO `lc_brands`;
-- --------------------------------------------------------
RENAME TABLE `lc_manufacturers_info` TO `lc_brands_info`;
-- --------------------------------------------------------
ALTER TABLE `lc_brands_info`
CHANGE COLUMN `manufacturer_id` `brand_id` INT(11) NOT NULL DEFAULT '0' AFTER `id`,
ADD UNIQUE INDEX `brand_info` (`brand_id`, `language_code`),
ADD INDEX `brand_id` (`brand_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_products`
ADD COLUMN `autofill_technical_data` TINYINT(1) NOT NULL DEFAULT '0' AFTER `image`,
CHANGE COLUMN `manufacturer_id` `brand_id` INT(11) NOT NULL DEFAULT '0' AFTER `status`,
DROP INDEX `manufacturer_id`,
ADD INDEX `brand_id` (`brand_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_images`
CHANGE COLUMN `checksum` `checksum` VARCHAR(32) NOT NULL DEFAULT '';
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `key` = REGEXP_REPLACE(`key`, '^store_', 'site_'),
  title = REPLACE(title, 'Store', 'Site'),
  description = REPLACE(description, 'store', 'site')
WHERE `key` REGEXP '^store_';
-- --------------------------------------------------------
UPDATE `lc_settings_groups`
SET `key` = 'site_info',
  title = 'Site Info',
  description = 'Site information'
WHERE `key` = 'store_info'
LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `setting_group_key` = 'site_info' WHERE `setting_group_key` = 'store_info';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = '0' WHERE `key` = 'cache_clear_thumbnails' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = REGEXP_REPLACE(`value`, '\.catalog$', '') WHERE `key` = 'site_template' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `key` = 'template', title = 'Template' WHERE `key` = 'site_template_catalog';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `key` = 'template_settings', title = 'Template Settings' WHERE `key` = 'site_template_catalog_settings';
-- --------------------------------------------------------
DELETE FROM `lc_settings` WHERE `key` IN ('site_template_admin', 'site_template_admin_settings', 'gzip_enabled', 'round_amounts');
-- --------------------------------------------------------
ALTER TABLE `lc_settings`
CHANGE COLUMN `key` `key` VARCHAR(64) NULL DEFAULT NULL DEFAULT '' AFTER `type`,
CHANGE COLUMN `value` `value` VARCHAR(8192) NOT NULL DEFAULT '' AFTER `key`,
ADD INDEX `type` (`type`);
-- --------------------------------------------------------
ALTER TABLE `lc_products`
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` DECIMAL(10,4) NOT NULL DEFAULT '0' AFTER `weight_unit`,
CHANGE COLUMN `dim_y` `width` DECIMAL(10,4) NOT NULL DEFAULT '0' AFTER `length`,
CHANGE COLUMN `dim_z` `height` DECIMAL(10,4) NOT NULL DEFAULT '0' AFTER `width`,
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '';
-- --------------------------------------------------------
ALTER TABLE `lc_orders`
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '';
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items`
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` DECIMAL(11,4) NOT NULL DEFAULT '0' AFTER `weight_unit`,
CHANGE COLUMN `dim_y` `width` DECIMAL(11,4) NOT NULL DEFAULT '0' AFTER `length`,
CHANGE COLUMN `dim_z` `height` DECIMAL(11,4) NOT NULL DEFAULT '0' AFTER `width`,
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '';
-- --------------------------------------------------------
ALTER TABLE `lc_settings`
ADD COLUMN `required` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `function`;
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `key` = 'site_weight_unit',
  `title` = 'Site Weight Unit',
  `description` = 'The prefered weight unit.'
WHERE `key` = 'site_length_class' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `key` = 'site_length_unit',
  `title` = 'Site Length Unit',
  `description` = 'The prefered length unit.'
WHERE `key` = 'site_weight_class' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `required` = 1
WHERE `key` IN (
  'site_email', 'site_name', 'site_language_code','site_currency_code', 'site_weight_unit', 'site_length_unit', 'site_timezone',
  'default_language_code', 'default_currency_code', 'default_country_code', 'default_zone_code', 'template'
);
-- --------------------------------------------------------
ALTER TABLE `lc_settings`
CHANGE COLUMN `setting_group_key` `group_key` VARCHAR(64) NOT NULL DEFAULT '',
DROP INDEX `setting_group_key`,
ADD INDEX `group_key` (`group_key`);
-- --------------------------------------------------------
UPDATE `lc_modules` SET `settings` = REPLACE(settings, 'weight_class', 'weight_unit') WHERE `module_id` = 'sm_zone_weight' LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES
('social_media', 'Social Media', 'Social media related settings.', 30);
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('advanced', 'global', 'Static Content Domain Name', 'Use the given alias domain name for static content (fonts, images, stylesheets, javascripts, etc.).', 'static_domain', '', 'text()', 12, NOW(), NOW()),
('social_media', 'global', 'Facebook Link', 'The link to your Facebook page.', 'facebook_link', '', 'url()', 10, NOW(), NOW()),
('social_media', 'global', 'Instagram Link', 'The link to your Instagram page.', 'instagram_link', '', 'url()', 20, NOW(), NOW()),
('social_media', 'global', 'LinkedIn Link', 'The link to your LinkedIn page.', 'linkedin_link', '', 'url()', 30, NOW(), NOW()),
('social_media', 'global', 'Pinterest Link', 'The link to your Pinterest page.', 'pinterest_link', '', 'url()', 40, NOW(), NOW()),
('social_media', 'global', 'Twitter Link', 'The link to your Twitter page.', 'twitter_link', '', 'url()', 50, NOW(), NOW()),
('social_media', 'global', 'YouTube Link', 'The link to your YouTube channel.', 'youtube_link', '', 'url()', 60, NOW(), NOW());
-- --------------------------------------------------------
CREATE TABLE `lc_stock_items` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`brand_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`supplier_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`code` VARCHAR(32) NOT NULL DEFAULT '',
	`sku` VARCHAR(32) NOT NULL DEFAULT '',
	`mpn` VARCHAR(32) NOT NULL DEFAULT '',
	`gtin` VARCHAR(32) NOT NULL DEFAULT '',
	`taric` VARCHAR(16) NOT NULL DEFAULT '',
	`image` VARCHAR(512) NOT NULL DEFAULT '',
	`file` VARCHAR(128) NOT NULL DEFAULT '',
	`filename` VARCHAR(128) NOT NULL DEFAULT '',
	`mime_type` VARCHAR(32) NOT NULL DEFAULT '',
	`downloads` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`quantity` DECIMAL(11,4) NOT NULL DEFAULT '0.0000',
	`quantity_unit_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`ordered` DECIMAL(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`weight` DECIMAL(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
	`length` DECIMAL(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`width` DECIMAL(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`height` DECIMAL(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`length_unit` VARCHAR(2) NOT NULL DEFAULT '',
	`purchase_price` DECIMAL(11,4) NOT NULL DEFAULT '0.0000',
	`purchase_price_currency_code` VARCHAR(3) NOT NULL DEFAULT '',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `sku` (`sku`),
	INDEX `brand_id` (`brand_id`),
	INDEX `supplier_id` (`supplier_id`),
	INDEX `mpn` (`mpn`),
	INDEX `gtin` (`gtin`),
	INDEX `code` (`code`)
);
-- --------------------------------------------------------
CREATE TABLE `lc_stock_transactions` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	`notes` MEDIUMTEXT NOT NULL DEFAULT '',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
);
-- --------------------------------------------------------
CREATE TABLE `lc_stock_transactions_contents` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`transaction_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`stock_item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`warehouse_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`quantity_adjustment` DECIMAL(11,4) NOT NULL DEFAULT '0.0000',
	PRIMARY KEY (`id`)
);