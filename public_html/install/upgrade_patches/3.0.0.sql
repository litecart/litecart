CREATE TABLE `lc_banners` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
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
CREATE TABLE `lc_shopping_carts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` VARCHAR(13) NOT NULL DEFAULT '',
  `customer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `customer_email` VARCHAR(128) NOT NULL DEFAULT '',
  `customer_company` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_tax_id` VARCHAR(32) NOT NULL DEFAULT '',
  `customer_address1` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_address2` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_city` VARCHAR(32) NOT NULL DEFAULT '',
  `customer_postcode` VARCHAR(8) NOT NULL DEFAULT '',
  `customer_country_code` VARCHAR(2) NOT NULL DEFAULT '',
  `customer_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `customer_phone` VARCHAR(24) NOT NULL DEFAULT '',
  `different_shipping_address` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `shipping_company` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address1` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address2` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_city` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_postcode` VARCHAR(8) NOT NULL DEFAULT '',
  `shipping_country_code` VARCHAR(2) NOT NULL DEFAULT '',
  `shipping_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `shipping_phone` VARCHAR(24) NOT NULL DEFAULT '',
  `shipping_option_id` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_option_userdata` VARCHAR(512) NOT NULL DEFAULT '',
  `payment_option_id` VARCHAR(32) NOT NULL DEFAULT '',
  `payment_option_userdata` VARCHAR(512) NOT NULL DEFAULT '',
  `weight_total` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
  `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `currency_code` VARCHAR(3) NOT NULL DEFAULT '',
  `lock_prices` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `display_prices_including_tax` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `subtotal` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
  `subtotal_tax` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
  `client_ip` VARCHAR(39) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(256) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `date_created` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
);
-- --------------------------------------------------------
CREATE TABLE `lc_products_to_stock_items` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `stock_item_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `price_adjust` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
  `price_operator` VARCHAR(1) NOT NULL DEFAULT '',
  `priority` TINYINT(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `stock_option` (`product_id`, `stock_item_id`),
  INDEX `product_id` (`product_id`)
);
-- --------------------------------------------------------
CREATE TABLE `lc_stock_items_references` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stock_item_id` INT UNSIGNED NOT NULL,
  `source_type` VARCHAR(32) NOT NULL DEFAULT '',
  `source` VARCHAR(32) NOT NULL DEFAULT '',
  `type` VARCHAR(32) NOT NULL DEFAULT '',
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `stock_item_id` (`stock_item_id`),
  INDEX `type` (`type`),
  INDEX `source` (`source`),
  INDEX `source_type` (`source_type`),
  UNIQUE INDEX `code` (`code`, `type`, `source`, `source_type`, `stock_item_id`)
);
-- --------------------------------------------------------
CREATE TABLE `lc_stock_transactions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `description` MEDIUMTEXT NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
-- --------------------------------------------------------
CREATE TABLE `lc_stock_transactions_contents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `stock_item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `quantity_adjustment` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`)
);
-- --------------------------------------------------------
RENAME TABLE `lc_cart_items` TO `lc_shopping_carts_items`;
-- --------------------------------------------------------
RENAME TABLE `lc_manufacturers` TO `lc_brands`;
-- --------------------------------------------------------
RENAME TABLE `lc_manufacturers_info` TO `lc_brands_info`;
-- --------------------------------------------------------
RENAME TABLE `lc_products_options` TO `lc_products_configurations`;
-- --------------------------------------------------------
RENAME TABLE `lc_products_options_values` TO `lc_products_configurations_values`;
-- --------------------------------------------------------
RENAME TABLE `lc_products_options_stock` TO `lc_stock_items`;
-- --------------------------------------------------------
ALTER TABLE `lc_brands_info`
CHANGE COLUMN `manufacturer_id` `brand_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
ADD INDEX `brand_id` (`brand_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_categories`
DROP COLUMN `list_style`;
-- --------------------------------------------------------
ALTER TABLE `lc_customers`
CHANGE COLUMN `last_ip` `last_ip_address` VARCHAR(39) NOT NULL DEFAULT '',
CHANGE COLUMN `last_host` `last_hostname` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `last_agent` `last_user_agent` VARCHAR(256) NOT NULL DEFAULT '';
-- --------------------------------------------------------
ALTER TABLE `lc_emails`
DROP COLUMN `charset`;
-- --------------------------------------------------------
ALTER TABLE `lc_languages`
DROP COLUMN `charset`;
-- --------------------------------------------------------
ALTER TABLE `lc_products`
ADD COLUMN `autofill_technical_data` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `image`,
CHANGE COLUMN `manufacturer_id` `brand_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_y` `width` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_z` `height` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
DROP COLUMN `upc`,
DROP INDEX `manufacturer_id`,
ADD INDEX `brand_id` (`brand_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_products_attributes`
ADD COLUMN `priority` INT NOT NULL DEFAULT 0 AFTER `custom_value`;
-- --------------------------------------------------------
ALTER TABLE `lc_products_images`
CHANGE COLUMN `checksum` `checksum` VARCHAR(32) NOT NULL DEFAULT '';
-- --------------------------------------------------------
ALTER TABLE `lc_orders`
DROP COLUMN `uid`,
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `payment_due` `total` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
CHANGE COLUMN `tax_total` `total_tax` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
ADD COLUMN `no` VARCHAR(16) NOT NULL DEFAULT '' AFTER `id`,
ADD COLUMN `subtotal` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `display_prices_including_tax`,
ADD COLUMN `subtotal_tax` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `subtotal`,
ADD COLUMN `discount` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `subtotal_tax`,
ADD COLUMN `discount_tax` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `discount`,
ADD COLUMN `shipping_option_userdata` VARCHAR(512) NOT NULL DEFAULT '' AFTER `shipping_option_name`,
ADD COLUMN `shipping_option_fee` FLOAT(11,4) NOT NULL DEFAULT 0 AFTER `shipping_option_userdata`,
ADD COLUMN `shipping_option_tax` FLOAT(11,4) NOT NULL DEFAULT 0 AFTER `shipping_option_fee`,
ADD COLUMN `shipping_progress` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `shipping_tracking_url`,
ADD COLUMN `shipping_current_status` VARCHAR(64) NOT NULL DEFAULT '' AFTER `shipping_progress`,
ADD COLUMN `shipping_current_location` VARCHAR(128) NOT NULL DEFAULT '' AFTER `shipping_current_status`,
ADD COLUMN `payment_option_userdata` VARCHAR(512) NOT NULL DEFAULT '' AFTER `payment_option_name`,
ADD COLUMN `payment_option_fee` FLOAT(11,4) NOT NULL DEFAULT 0 AFTER `payment_option_userdata`,
ADD COLUMN `payment_option_tax` FLOAT(11,4) NOT NULL DEFAULT 0 AFTER `payment_option_fee`,
ADD COLUMN `payment_receipt_url` VARCHAR(256) NOT NULL DEFAULT '' AFTER `payment_transaction_id`,
ADD COLUMN `payment_terms` VARCHAR(8) NOT NULL DEFAULT '' AFTER `payment_receipt_url`,
ADD COLUMN `incoterm` VARCHAR(3) NOT NULL DEFAULT '' AFTER `payment_receipt_url`,
ADD COLUMN `date_paid` TIMESTAMP NULL DEFAULT NULL AFTER `public_key`,
ADD COLUMN `date_dispatched` TIMESTAMP NULL DEFAULT NULL AFTER `date_paid`,
ADD INDEX `no` (`no`);
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items`
CHANGE COLUMN `data` `userdata` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_y` `width` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_z` `height` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `options` `configuration` VARCHAR(1024) NOT NULL DEFAULT '',
CHANGE COLUMN `option_stock_combination` `attributes` VARCHAR(32) NOT NULL DEFAULT '',
ADD COLUMN `stock_item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `product_id`,
ADD COLUMN `discount` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `tax`,
ADD COLUMN `discount_tax` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `discount`,
ADD COLUMN `sum` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `discount_tax`,
ADD COLUMN `sum_tax` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `sum`,
ADD COLUMN `downloads` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `length_unit`,
ADD COLUMN `priority` INT NOT NULL DEFAULT '0' AFTER `downloads`,
ADD INDEX `product_id` (`product_id`),
ADD INDEX `stock_item_id` (`stock_item_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_orders_totals`
ADD COLUMN `discount` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `tax`
CHANGE COLUMN `value` `amount` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `title`;
-- --------------------------------------------------------
ALTER TABLE `lc_order_statuses`
ADD COLUMN `hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`,
ADD COLUMN `is_trackable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_archived`,
ADD COLUMN `stock_action` ENUM('none','reserve','withdraw') NOT NULL DEFAULT 'none' AFTER `is_trackable`,
ADD COLUMN `state` ENUM('','created','on_hold','ready','delayed','processing','dispatched','in_transit','delivered','returning','returned','cancelled','fraud') NOT NULL DEFAULT '' AFTER `id`,
DROP COLUMN `keywords`,
DROP COLUMN `priority`;
-- --------------------------------------------------------
UPDATE `lc_order_statuses`
SET stock_action = 'withdraw'
WHERE is_sale = 1;
-- --------------------------------------------------------
ALTER TABLE `lc_stock_items`
CHANGE COLUMN `product_id` `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `combination` `attributes` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `sku` `sku` VARCHAR(32) NOT NULL DEFAULT '',
ADD COLUMN `gtin` VARCHAR(32) NOT NULL DEFAULT '' AFTER `sku`,
ADD COLUMN `mpn` VARCHAR(32) NOT NULL DEFAULT '' AFTER `gtin`,
ADD COLUMN `taric` VARCHAR(32) NOT NULL DEFAULT '' AFTER `mpn`,
ADD COLUMN `image` VARCHAR(128) NOT NULL DEFAULT '' AFTER `taric`,
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
CHANGE COLUMN `dim_y` `width` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
CHANGE COLUMN `dim_z` `height` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
ADD COLUMN `purchase_price` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `length_unit`,
ADD COLUMN `purchase_price_currency_code` VARCHAR(3) NOT NULL DEFAULT '' AFTER `purchase_price`,
ADD COLUMN `quantity_unit_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `quantity`,
ADD COLUMN `reorder_point` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `quantity_unit_id`,
ADD COLUMN `backordered` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `reorder_point`,
ADD COLUMN `file` VARCHAR(128) NOT NULL DEFAULT '' AFTER `backordered`,
ADD COLUMN `filename` VARCHAR(128) NOT NULL DEFAULT '' AFTER `file`,
ADD COLUMN `mime_type` VARCHAR(32) NOT NULL DEFAULT '' AFTER `filename`,
ADD COLUMN `downloads` INT NOT NULL DEFAULT '0' AFTER `mime_type`,
DROP INDEX `product_option_stock`,
ADD INDEX `sku` (`sku`),
ADD INDEX `gtin` (`gtin`),
ADD INDEX `mpn` (`mpn`),
ADD FULLTEXT INDEX `name` (`name`);
-- --------------------------------------------------------
ALTER TABLE `lc_settings`
ADD COLUMN `required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `function`,
CHANGE COLUMN `setting_group_key` `group_key` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `key` `key` VARCHAR(64) NULL DEFAULT NULL DEFAULT '',
CHANGE COLUMN `value` `value` VARCHAR(8192) NOT NULL DEFAULT '',
ADD INDEX `type` (`type`),
ADD INDEX `group_key` (`group_key`),
DROP INDEX `setting_group_key`;
-- --------------------------------------------------------
ALTER TABLE `lc_shopping_carts_items`
ADD COLUMN `cart_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `customer_id`,
CHANGE COLUMN `data` `userdata` VARCHAR() NOT NULL DEFAULT '' AFTER `name`,
ADD COLUMN `type` ENUM('product','stock_item','custom') NOT NULL DEFAULT 'product' AFTER `cart_id`,
ADD COLUMN `code` VARCHAR(32) NOT NULL DEFAULT '' BEFORE `sku`,
ADD COLUMN `configuration` VARCHAR(512) NOT NULL DEFAULT '0',
ADD COLUMN `discount` FLOAT(11,4) NOT NULL DEFAULT '0.0000' AFTER `tax`,
ADD COLUMN `priority` INT NOT NULL DEFAULT 0 AFTER `length_unit`,
ADD INDEX `cart_id` (`cart_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_tax_rates`
CHANGE COLUMN `rate` `rate` FLOAT(4,2) NOT NULL DEFAULT '0.0000' AFTER `description`,
DROP COLUMN `type`;
-- --------------------------------------------------------
ALTER TABLE `lc_users`
CHANGE COLUMN `last_ip` `last_ip_address` VARCHAR(39) NOT NULL DEFAULT '',
CHANGE COLUMN `last_host` `last_hostname` VARCHAR(64) NOT NULL DEFAULT '',
ADD COLUMN `last_user_agent` VARCHAR(256) NOT NULL DEFAULT '' AFTER `last_hostname`;
-- --------------------------------------------------------
UPDATE `lc_modules` SET `settings` = REPLACE(settings, 'weight_class', 'weight_unit') WHERE `module_id` = 'sm_zone_weight' LIMIT 1;
-- --------------------------------------------------------
DELETE FROM `lc_modules` WHERE `module_id` = 'ot_subtotal' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_orders` SET `no` = id;
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `key` = REGEXP_REPLACE(`key`, '^store_', 'site_'),
  title = REPLACE(title, 'Store', 'Site'),
  description = REPLACE(description, 'store', 'site')
WHERE `key` REGEXP '^store_';
-- --------------------------------------------------------
UPDATE `lc_settings_groups`
SET `key` = 'site_info',
  name = 'Site Info',
  description = 'Site information'
WHERE `key` = 'store_info'
LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `group_key` = 'site_info' WHERE `group_key` = 'store_info';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = '0' WHERE `key` = 'cache_clear_thumbnails' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `key` = 'template', title = 'Template', `value` = REGEXP_REPLACE(`value`, '\.catalog$', '') WHERE `key` = 'site_template_catalog' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `key` = 'template_settings', title = 'Template Settings' WHERE `key` = 'site_template_catalog_settings';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `key` = 'site_weight_unit',  `title` = 'Site Weight Unit', `description` = 'The prefered weight unit.' WHERE `key` = 'site_length_class' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `key` = 'site_length_unit', `title` = 'Site Length Unit', `description` = 'The prefered length unit.' WHERE `key` = 'site_weight_class' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `required` = 1
WHERE `key` IN (
  'site_email', 'site_name', 'site_language_code','site_currency_code', 'site_weight_unit', 'site_length_unit', 'site_timezone',
  'default_language_code', 'default_currency_code', 'default_country_code', 'default_zone_code', 'template'
);
-- --------------------------------------------------------
INSERT INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES
('social_media', 'Social Media', 'Social media related settings.', 30);
-- --------------------------------------------------------
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
-- --------------------------------------------------------
INSERT IGNORE INTO `lc_shopping_carts` (uid, customer_id, date_updated, date_created)
SELECT cart_uid, customer_id, date_updated, date_created FROM `lc_shopping_carts_items`
GROUP BY cart_uid, customer_id
ORDER BY id DESC;
-- --------------------------------------------------------
UPDATE `lc_shopping_carts_items` sci
LEFT JOIN `lc_shopping_carts` sc on (sci.cart_uid = sc.uid)
SET sci.cart_id = sc.id;
-- --------------------------------------------------------
ALTER TABLE `lc_shopping_carts_items`
DROP COLUMN `cart_uid`,
DROP COLUMN `customer_id`,
DROP COLUMN `key`;
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `function` = 'regional_text()'
WHERE `function` = 'regional_input()';
-- --------------------------------------------------------
UPDATE `lc_orders` o
LEFT JOIN `lc_orders_totals` ot ON (ot.order_id = o.id AND ot.module_id = 'ot_subtotal')
SET o.subtotal = ot.`amount`,
o.subtotal_tax = ot.`tax`;
-- --------------------------------------------------------
UPDATE `lc_orders` o
LEFT JOIN (
  SELECT order_id, sum(`amount`) as discount, sum(`tax`) as discount_tax
  FROM `lc_orders_totals`
  WHERE `amount` < 0 AND calculate
  GROUP BY order_id
) ot ON (ot.order_id = o.id)
SET o.discount = 0 - if(ot.discount, ot.discount, 0),
o.discount_tax = 0 - if(ot.discount_tax, ot.discount_tax, 0);
-- --------------------------------------------------------
DELETE FROM `lc_orders_totals` WHERE module_id = 'ot_subtotal';
-- --------------------------------------------------------
UPDATE `lc_orders_items` oi
LEFT JOIN `lc_stock_items` si ON (si.product_id = oi.product_id AND si.attributes = oi.attributes)
SET oi.stock_item_id = si.id;
-- --------------------------------------------------------
UPDATE `lc_orders_items`
SET sum = price * quantity,
sum_tax = tax * quantity;
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items`
DROP COLUMN `tax`;
-- --------------------------------------------------------
INSERT INTO `lc_products_to_stock_items`
(product_id, stock_item_id)
SELECT product_id, id FROM `lc_stock_items`;
-- --------------------------------------------------------
DELETE FROM `lc_settings` WHERE `key` IN ('site_template_admin', 'site_template_admin_settings', 'gzip_enabled', 'round_amounts', 'cache_system_breakpoint', 'jobs_interval', 'jobs_last_push');
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `key` = 'jobs_last_push',
  `title` = 'Background Jobs Last Push',
  `description` = 'Time when background jobs were last pushed.'
WHERE `key` = 'jobs_last_run'
LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_stock_items` si
LEFT JOIN `lc_products` p on (p.id = si.product_id) SET
si.gtin = if(si.gtin != '', si.gtin, p.gtin),
si.mpn = if(si.mpn != '', si.mpn, p.mpn),
si.taric = if(si.taric != '', si.taric, p.taric),
si.weight = if (si.weight > 0, si.weight, p.weight),
si.weight_unit = if(si.weight_unit != '', si.weight_unit, p.weight_unit),
si.length = if (si.length > 0, si.length, p.length),
si.width = if (si.width > 0, si.width, p.width),
si.height = if (si.height > 0, si.height, p.height),
si.length_unit = if(si.length_unit != '', si.length_unit, p.length_unit),
si.purchase_price = p.purchase_price,
si.purchase_price_currency_code = p.purchase_price_currency_code,
si.quantity_unit_id = if(si.quantity_unit_id > 0, si.quantity_unit_id, p.quantity_unit_id);
-- --------------------------------------------------------
INSERT INTO `lc_stock_transactions` (id, name, description)
VALUES (1, 'Initial Stock Transaction', 'This is an initial system generated stock transaction to deposit stock for all sold items and items in stock. We need this for future inconcistency checks.');
-- --------------------------------------------------------
INSERT INTO `lc_stock_transactions_contents`
(transaction_id, stock_item_id, quantity_adjustment)
SELECT transaction_id, stock_item_id, quantity_adjustment FROM (
  SELECT '1' AS transaction_id, stock_item_id, SUM(quantity) as quantity_adjustment FROM (
    SELECT si.id AS stock_item_id, si.quantity FROM `lc_stock_items` si
    UNION
    SELECT oi.stock_item_id, oi.quantity FROM `lc_orders_items` oi
	 WHERE oi.order_id IN (
      SELECT id FROM `lc_orders` o
      WHERE o.order_status_id IN (
        SELECT id FROM `lc_order_statuses` os
        WHERE os.is_sale
      )
    )
  ) x
  GROUP BY x.stock_item_id
  ORDER BY x.stock_item_id
) y;
