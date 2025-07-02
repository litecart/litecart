CREATE TABLE `lc_banners` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`languages` VARCHAR(64) NOT NULL DEFAULT '',
	`html` TEXT NOT NULL DEFAULT '',
	`image` VARCHAR(64) NOT NULL DEFAULT '',
	`link` VARCHAR(255) NOT NULL DEFAULT '',
	`keywords` VARCHAR(255) NOT NULL DEFAULT '',
	`total_views` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`total_clicks` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`valid_from` TIMESTAMP NULL DEFAULT NULL,
	`valid_to` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE `lc_campaigns` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`name` VARCHAR(32) NOT NULL DEFAULT '',
	`valid_from` TIMESTAMP NULL DEFAULT NULL,
	`valid_to` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `valid_from` (`valid_from`) USING BTREE,
	INDEX `valid_to` (`valid_to`) USING BTREE,
	INDEX `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE `lc_campaigns_products` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`campaign_id` INT(10) UNSIGNED NOT NULL,
	`product_id` INT(10) UNSIGNED NOT NULL,
	`price` VARCHAR(512) NOT NULL DEFAULT '{}',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `product_id` (`product_id`) USING BTREE,
	INDEX `campaign_id` (`campaign_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE `lc_customer_groups` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` ENUM('retail', 'wholesale') NOT NULL DEFAULT 'retail',
	`name` VARCHAR(32) NOT NULL DEFAULT '',
	`description` VARCHAR(248) NOT NULL DEFAULT '',
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE IF NOT EXISTS `lc_customers_activity` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`session_id` VARCHAR(64) NULL,
	`customer_id` INT(10) UNSIGNED NULL,
	`type` VARCHAR(64) NULL,
	`description` VARCHAR(248) NOT NULL DEFAULT '',
	`data` VARCHAR(1024) NULL,
	`url` VARCHAR(248) NULL,
	`ip_address` VARCHAR(39) NULL,
	`hostname` VARCHAR(128) NULL,
	`user_agent` VARCHAR(248) NULL,
	`fingerprint` VARCHAR(32) NULL,
	`expires_at` TIMESTAMP NULL DEFAULT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `customer_id` (`customer_id`) USING BTREE,
	INDEX `session_id` (`session_id`) USING BTREE,
	INDEX `ip_address` (`ip_address`) USING BTREE,
	INDEX `hostname` (`hostname`) USING BTREE,
	INDEX `user_agent` (`user_agent`) USING BTREE,
	INDEX `fingerprint` (`fingerprint`) USING BTREE,
	INDEX `expires_at` (`expires_at`) USING BTREE
);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE `lc_redirects` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`immediate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`pattern` VARCHAR(248) NOT NULL DEFAULT '',
	`destination` VARCHAR(248) NOT NULL DEFAULT '',
	`http_response_code` enum('301','302') NOT NULL DEFAULT '301',
	`total_redirects` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`last_redirected` TIMESTAMP NULL,
	`valid_from` TIMESTAMP NULL,
	`valid_to` TIMESTAMP NULL,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `pattern` (`pattern`),
	KEY `status` (`status`),
	KEY `immediate` (`immediate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE `lc_site_tags` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) NOT NULL DEFAULT '0',
	`position` ENUM('head','body') NOT NULL DEFAULT 'head',
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	`content` TEXT NOT NULL DEFAULT '',
	`require_consent` VARCHAR(64) NULL,
	`priority` TINYINT(4) NOT NULL DEFAULT '0',
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `status` (`status`),
	INDEX `position` (`position`),
	INDEX `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE `lc_stock_items` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`brand_id` INT(10) UNSIGNED NULL,
	`supplier_id` INT(10) UNSIGNED NULL,
	`name` TEXT NOT NULL DEFAULT '{}',
	`sku` VARCHAR(32) NOT NULL DEFAULT '',
	`mpn` VARCHAR(32) NOT NULL DEFAULT '',
	`gtin` VARCHAR(32) NOT NULL DEFAULT '',
	`shelf` VARCHAR(32) NOT NULL DEFAULT '',
	`taric` VARCHAR(16) NOT NULL DEFAULT '',
	`image` VARCHAR(512) NOT NULL DEFAULT '',
	`weight` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
	`length` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`width` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`height` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`length_unit` VARCHAR(2) NOT NULL DEFAULT '',
	`quantity` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`quantity_unit_id` INT(11) UNSIGNED NULL,
	`backordered` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`purchase_price` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`purchase_price_currency_code` VARCHAR(3) NOT NULL DEFAULT '',
	`file` VARCHAR(248) NULL,
	`filename` VARCHAR(64) NULL,
	`mime_type` VARCHAR(32) NULL,
	`downloads` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `mpn` (`mpn`) USING BTREE,
	INDEX `gtin` (`gtin`) USING BTREE,
	INDEX `sku` (`sku`) USING BTREE,
	INDEX `brand_id` (`brand_id`) USING BTREE,
	INDEX `supplier_id` (`supplier_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE `lc_stock_transactions` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	`description` MEDIUMTEXT NOT NULL DEFAULT '',
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE `lc_stock_transactions_contents` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`transaction_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`stock_item_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`quantity_adjustment` FLOAT(11,4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	INDEX `transaction_id` (`transaction_id`),
	INDEX `stock_item_id` (`stock_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
CREATE TABLE `lc_third_parties` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`privacy_classes` VARCHAR(64) NOT NULL DEFAULT '',
	`category` VARCHAR(64) NOT NULL DEFAULT '',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`description` MEDIUMTEXT NOT NULL DEFAULT '{}',
	`collected_data` TEXT NOT NULL DEFAULT '{}',
	`purposes` TEXT NOT NULL DEFAULT '{}',
	`homepage` VARCHAR(248) NOT NULL DEFAULT '',
	`cookie_policy_url` VARCHAR(248) NOT NULL DEFAULT '',
	`privacy_policy_url` VARCHAR(248) NOT NULL DEFAULT '',
	`opt_out_url` VARCHAR(248) NOT NULL DEFAULT '',
	`do_not_sell_url` VARCHAR(248) NOT NULL DEFAULT '',
	`country_code` CHAR(2) NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `status` (`status`) USING BTREE,
	INDEX `country_code` (`country_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -----
RENAME TABLE `lc_manufacturers` TO `lc_brands`;
-- -----
RENAME TABLE `lc_manufacturers_info` TO `lc_brands_info`;
-- -----
RENAME TABLE `lc_products_options` TO `lc_products_customizations`;
-- -----
RENAME TABLE `lc_products_options_values` TO `lc_products_customizations_values`;
-- -----
RENAME TABLE `lc_products_options_stock` TO `lc_products_stock_options`;
-- -----
RENAME TABLE `lc_users` TO `lc_administrators`;
-- -----
ALTER TABLE `lc_administrators`
CHANGE COLUMN `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `last_ip` `last_ip_address` VARCHAR(39) NOT NULL DEFAULT '',
CHANGE COLUMN `last_host` `last_hostname` VARCHAR(64) NOT NULL DEFAULT '',
ADD COLUMN `firstname` VARCHAR(32) NOT NULL DEFAULT '' AFTER `username`,
ADD COLUMN `lastname` VARCHAR(32) NOT NULL DEFAULT '' AFTER `firstname`,
ADD COLUMN `two_factor_auth` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `widgets`,
ADD COLUMN `last_user_agent` VARCHAR(255) NOT NULL DEFAULT '' AFTER `last_hostname`,
ADD COLUMN `known_ips` VARCHAR(512) NOT NULL DEFAULT '' AFTER `two_factor_auth`,
CHANGE COLUMN `login_attempts` `login_attempts` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `two_factor_auth`,
CHANGE COLUMN `total_logins` `total_logins` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `login_attempts`,
CHANGE COLUMN `date_active` `last_active` TIMESTAMP NULL DEFAULT NULL AFTER `last_user_agent`,
CHANGE COLUMN `date_login` `last_login` TIMESTAMP NULL DEFAULT NULL AFTER `last_active`,
CHANGE COLUMN `date_valid_from` `valid_from` TIMESTAMP NULL DEFAULT NULL AFTER `known_ips`,
CHANGE COLUMN `date_valid_to` `valid_to` TIMESTAMP NULL DEFAULT NULL AFTER `valid_from`;
-- ------
ALTER TABLE `lc_attribute_groups`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
ADD COLUMN `name` TEXT NOT NULL DEFAULT '{}' AFTER `code`;
-- -----
ALTER TABLE `lc_attribute_groups_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `group_id` `group_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL;
-- -----
ALTER TABLE `lc_attribute_values`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `group_id` `group_id` INT(10) UNSIGNED NOT NULL,
ADD COLUMN `name` TEXT NOT NULL DEFAULT '{}' AFTER `group_id`;
-- -----
ALTER TABLE `lc_attribute_values_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `value_id` `value_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL;
-- -----
ALTER TABLE `lc_brands`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `featured` `featured` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `short_description` TEXT NOT NULL DEFAULT '{}' AFTER `name`,
ADD COLUMN `description` MEDIUMTEXT NOT NULL DEFAULT '{}' AFTER `short_description`,
ADD COLUMN `h1_title` TEXT NOT NULL DEFAULT '{}' AFTER `description`,
ADD COLUMN `head_title` TEXT NOT NULL DEFAULT '{}' AFTER `h1_title`,
ADD COLUMN `meta_description` TEXT NOT NULL DEFAULT '{}' AFTER `head_title`,
ADD COLUMN `link` TEXT NOT NULL DEFAULT '{}' AFTER `meta_description`,
ADD INDEX `name` (`name`);
-- -----
ALTER TABLE `lc_brands_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `manufacturer_id` `brand_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL,
ADD INDEX `brand_id` (`brand_id`);
-- -----
ALTER TABLE `lc_cart_items`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `cart_uid` `cart_uid` VARCHAR(13) NOT NULL AFTER `id`,
CHANGE COLUMN `customer_id` `customer_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `key` `key` VARCHAR(32) NOT NULL,
CHANGE COLUMN `product_id` `product_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `options` `userdata` VARCHAR(2048) NOT NULL DEFAULT '',
ADD COLUMN `stock_option_id` INT(10) UNSIGNED NULL AFTER `product_id`,
ADD COLUMN `image` VARCHAR(255) NOT NULL DEFAULT '' AFTER `userdata`;
-- -----
ALTER TABLE `lc_categories`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `parent_id` `parent_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `google_taxonomy_id` `google_taxonomy_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `status` `status` TINYINT(1) UNSIGNED NOT NULL,
ADD COLUMN `name` TEXT NOT NULL DEFAULT '' AFTER `code`,
ADD COLUMN `short_description` TEXT NOT NULL DEFAULT '{}' AFTER `name`,
ADD COLUMN `description` MEDIUMTEXT NOT NULL DEFAULT '{}' AFTER `short_description`,
ADD COLUMN `synonyms` TEXT NOT NULL DEFAULT '{}' AFTER `description`,
ADD COLUMN `head_title` TEXT NOT NULL DEFAULT '{}' AFTER `synonyms`,
ADD COLUMN `h1_title` TEXT NOT NULL DEFAULT '{}' AFTER `head_title`,
ADD COLUMN `meta_description` TEXT NOT NULL DEFAULT '{}' AFTER `h1_title`;
-- -----
ALTER TABLE `lc_categories_filters`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `category_id` `category_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `attribute_group_id` `attribute_group_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `select_multiple` `select_multiple` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
-- ------
ALTER TABLE `lc_categories_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `category_id` `category_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL,
ADD COLUMN `synonyms` VARCHAR(248) NOT NULL DEFAULT '' AFTER `description`,
ADD FULLTEXT INDEX `name` (`name`),
ADD FULLTEXT INDEX `short_description` (`short_description`),
ADD FULLTEXT INDEX `description` (`description`),
ADD FULLTEXT INDEX `synonyms` (`synonyms`);
-- ------
ALTER TABLE `lc_countries`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `iso_code_1` `iso_code_1` CHAR(3) NOT NULL DEFAULT '',
CHANGE COLUMN `iso_code_2` `iso_code_2` CHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `iso_code_3` `iso_code_3` CHAR(3) NOT NULL DEFAULT '',
CHANGE COLUMN `postcode_format` `postcode_format` VARCHAR(255) NOT NULL DEFAULT '',
CHANGE COLUMN `postcode_required` `postcode_required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_currencies`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `status` `status` TINYINT(1) NOT NULL DEFAULT '0',
CHANGE COLUMN `value` `value` FLOAT(11,6) UNSIGNED NOT NULL DEFAULT '1.000000',
CHANGE COLUMN `decimals` `decimals` TINYINT(1) UNSIGNED NOT NULL DEFAULT '2',
ADD INDEX `code` (`code`),
ADD INDEX `number` (`number`);
-- -----
ALTER TABLE `lc_customers`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `email` `email` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `tax_id` `tax_id` VARCHAR(24) NOT NULL DEFAULT '',
CHANGE COLUMN `company` `company` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `firstname` `firstname` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `lastname` `lastname` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `address1` `address1` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `address2` `address2` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `postcode` `postcode` VARCHAR(16) NOT NULL DEFAULT '',
CHANGE COLUMN `city` `city` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `country_code` `country_code` CHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `zone_code` `zone_code` VARCHAR(8) NOT NULL DEFAULT '',
CHANGE COLUMN `phone` `phone` VARCHAR(16) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_company` `shipping_company` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_firstname` `shipping_firstname` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_lastname` `shipping_lastname` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_address1` `shipping_address1` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_address2` `shipping_address2` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_postcode` `shipping_postcode` VARCHAR(16) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_city` `shipping_city` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_country_code` `shipping_country_code` CHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_zone_code` `shipping_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_phone` `shipping_phone` VARCHAR(16) NOT NULL DEFAULT '',
CHANGE COLUMN `login_attempts` `login_attempts` INT(11) NOT NULL DEFAULT '0',
CHANGE COLUMN `total_logins` `total_logins` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `last_ip` `last_ip_address` VARCHAR(39) NOT NULL DEFAULT '',
CHANGE COLUMN `last_host` `last_hostname` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `last_agent` `last_user_agent` VARCHAR(255) NOT NULL DEFAULT '',
CHANGE COLUMN `date_login` `last_login` TIMESTAMP NULL DEFAULT NULL AFTER `last_user_agent`,
CHANGE COLUMN `date_blocked_until` `blocked_until` TIMESTAMP NULL DEFAULT NULL AFTER `last_login`,
ADD COLUMN `shipping_email` VARCHAR(64) NOT NULL DEFAULT '' AFTER `shipping_phone`,
ADD COLUMN `group_id` INT UNSIGNED NULL AFTER `id`,
ADD COLUMN `known_ips` VARCHAR(512) NOT NULL DEFAULT '' AFTER `total_logins`,
ADD COLUMN `last_active` TIMESTAMP NULL DEFAULT NULL AFTER `last_login`,
ADD INDEX `group_id` (`group_id`);
-- -----
ALTER TABLE `lc_delivery_statuses`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
ADD COLUMN `name` TEXT NOT NULL DEFAULT '{}' AFTER `id`,
ADD COLUMN `description` TEXT NOT NULL DEFAULT '{}' AFTER `name`;
-- -----
ALTER TABLE `lc_delivery_statuses_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `delivery_status_id` `delivery_status_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL;
-- -----
ALTER TABLE `lc_emails`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
ADD COLUMN `ip_address` VARCHAR(39) NOT NULL DEFAULT '' AFTER `multiparts`,
ADD COLUMN `hostname` VARCHAR(128) NOT NULL DEFAULT '' AFTER `ip_address`,
ADD COLUMN `user_agent` VARCHAR(248) NOT NULL DEFAULT '' AFTER `hostname`,
ADD INDEX `status` (`status`);
-- -----
ALTER TABLE `lc_geo_zones`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
-- -----
ALTER TABLE `lc_languages`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `locale` `locale` VARCHAR(64) NOT NULL DEFAULT '' AFTER `name`,
ADD COLUMN `locale_intl` VARCHAR(16) NOT NULL DEFAULT '' AFTER `locale`,
ADD COLUMN `mysql_collation` VARCHAR(32) NOT NULL DEFAULT '' AFTER `locale_intl`,
ADD INDEX `code` (`code`),
ADD INDEX `code2` (`code2`);
-- -----
ALTER TABLE `lc_modules`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `date_pushed` `last_pushed` TIMESTAMP NULL DEFAULT NULL AFTER `last_log`,
CHANGE COLUMN `date_processed` `last_processed` TIMESTAMP NULL DEFAULT NULL AFTER `last_pushed`;
-- -----
ALTER TABLE `lc_newsletter_recipients`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `client_ip` `ip_address` VARCHAR(39) NOT NULL DEFAULT '',
ADD COLUMN `subscribed` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `id`,
ADD COLUMN `country_code` CHAR(2) NULL AFTER `email`,
ADD COLUMN `language_code` CHAR(2) NULL AFTER `country_code`,
ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `user_agent`,
ADD INDEX `subscribed` (`subscribed`);
-- -----
ALTER TABLE `lc_orders`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `starred` `starred` TINYINT(1) UNSIGNED,
CHANGE COLUMN `unread` `unread` TINYINT(1) UNSIGNED,
CHANGE COLUMN `order_status_id` `order_status_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `customer_id` `customer_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `customer_email` `customer_email` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_tax_id` `customer_tax_id` VARCHAR(24) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_company` `customer_company` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_firstname` `customer_firstname` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_lastname` `customer_lastname` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_address1` `customer_address1` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_address2` `customer_address2` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_city` `customer_city` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_postcode` `customer_postcode` VARCHAR(8) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_country_code` `customer_country_code` CHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_zone_code` `customer_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
CHANGE COLUMN `customer_phone` `customer_phone` VARCHAR(16) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_company` `shipping_company` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_firstname` `shipping_firstname` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_lastname` `shipping_lastname` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_address1` `shipping_address1` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_address2` `shipping_address2` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_city` `shipping_city` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_postcode` `shipping_postcode` VARCHAR(8) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_country_code` `shipping_country_code` CHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_zone_code` `shipping_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
CHANGE COLUMN `shipping_phone` `shipping_phone` VARCHAR(16) NOT NULL DEFAULT '',
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `language_code` `language_code` CHAR(2) NULL AFTER `reference`,
CHANGE COLUMN `payment_due` `total` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `tax_total` `total_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `client_ip` `ip_address` VARCHAR(39) NOT NULL DEFAULT '',
ADD COLUMN `no` VARCHAR(16) NOT NULL DEFAULT '' AFTER `id`,
ADD COLUMN `shipping_tax_id` VARCHAR(128) NOT NULL DEFAULT '' AFTER `customer_email`,
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
ADD COLUMN `utm_data` VARCHAR(1024) NOT NULL DEFAULT '{}' AFTER `notes`,
ADD COLUMN `hostname` VARCHAR(128) NOT NULL DEFAULT '' AFTER `ip_address`,
ADD INDEX `no` (`no`);
-- -----
ALTER TABLE `lc_orders_comments`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `order_id` `order_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `author_id` `author_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `hidden` `hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_orders_items`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `order_id` `order_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `product_id` `product_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_y` `width` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_z` `height` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `options` `userdata` VARCHAR(2048) NULL AFTER `name`,
CHANGE COLUMN `option_stock_combination` `attributes` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `priority` `priority` INT NOT NULL DEFAULT '0',
ADD COLUMN `stock_option_id` INT(10) UNSIGNED NULL AFTER `product_id`,
ADD COLUMN `stock_items` INT(10) UNSIGNED NULL AFTER `stock_option_id`,
ADD COLUMN `serial_number` VARCHAR(32) NOT NULL DEFAULT '' AFTER `name`,
ADD COLUMN `tax_rate` FLOAT(4,2) UNSIGNED NULL AFTER `tax`,
ADD COLUMN `tax_class_id` INT(10) UNSIGNED NULL AFTER `tax_rate`,
ADD COLUMN `discount` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `tax_class_id`,
ADD COLUMN `discount_tax` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `discount`,
ADD COLUMN `sum` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `discount_tax`,
ADD COLUMN `sum_tax` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `sum`,
ADD COLUMN `downloads` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `length_unit`,
ADD INDEX `product_id` (`product_id`),
ADD INDEX `stock_option_id` (`stock_option_id`),
ADD INDEX `stock_items` (`stock_items`);
-- -----
ALTER TABLE `lc_orders_totals`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `order_id` `order_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `value` `amount` FLOAT(11,4) NOT NULL DEFAULT '0',
CHANGE COLUMN `calculate` `calculate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `tax_rate` FLOAT(6,4) UNSIGNED NOT NULL DEFAULT '0.0000',
ADD COLUMN `discount` FLOAT(11,4) NOT NULL DEFAULT '0' AFTER `amount`;
-- -----
ALTER TABLE `lc_order_statuses`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `is_sale` `is_sale` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `is_archived` `is_archived` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `is_trackable` `is_trackable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `notify` `notify` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`,
ADD COLUMN `name` TEXT NOT NULL DEFAULT '{}' AFTER `color`,
ADD COLUMN `description` VARCHAR(255) NOT NULL DEFAULT '' AFTER `name`,
ADD COLUMN `email_subject` VARCHAR(128) NOT NULL DEFAULT '' AFTER `description`,
ADD COLUMN `email_message` TEXT NOT NULL DEFAULT '' AFTER `email_subject`;
-- -----
ALTER TABLE `lc_order_statuses_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `order_status_id` `order_status_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL;
-- -----
ALTER TABLE `lc_pages`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `parent_id` `parent_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `title` TEXT NOT NULL DEFAULT '{}' AFTER `dock`,
ADD COLUMN `content` MEDIUMTEXT NOT NULL DEFAULT '' AFTER `title`,
ADD COLUMN `head_title` TEXT NOT NULL DEFAULT '' AFTER `content`,
ADD COLUMN `meta_description` TEXT NOT NULL DEFAULT '' AFTER `head_title`;
-- -----
ALTER TABLE `lc_pages_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `page_id` `page_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL;
-- -----
ALTER TABLE `lc_products`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `manufacturer_id` `brand_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `supplier_id` `supplier_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `delivery_status_id` `delivery_status_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `sold_out_status_id` `sold_out_status_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `default_category_id` `default_category_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `keywords` `keywords` VARCHAR(248) NOT NULL DEFAULT '',
CHANGE COLUMN `quantity_min` `quantity_min` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '1.0000',
CHANGE COLUMN `quantity_max` `quantity_max` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0.0000',
CHANGE COLUMN `quantity_step` `quantity_step` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0.0000',
CHANGE COLUMN `quantity_unit_id` `quantity_unit_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `weight` `weight` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_y` `width` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_z` `height` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `tax_class_id` `tax_class_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `views` `views` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `purchases` `purchases` INT(10) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `featured` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `status`,
ADD COLUMN `name` TEXT NOT NULL DEFAULT '{}' AFTER `default_category_id`,
ADD COLUMN `short_description` TEXT NOT NULL DEFAULT '{}' AFTER `name`,
ADD COLUMN `description` MEDIUMTEXT NOT NULL DEFAULT '{}' AFTER `short_description`,
ADD COLUMN `technical_data` TEXT NOT NULL DEFAULT '{}' AFTER `description`,
ADD COLUMN `autofill_technical_data` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `technical_data`,
ADD COLUMN `synonyms` TEXT NOT NULL DEFAULT '{}' AFTER `description`,
ADD COLUMN `head_title` TEXT NOT NULL DEFAULT '{}' AFTER `synonyms`,
ADD COLUMN `meta_description` TEXT NOT NULL DEFAULT '{}' AFTER `head_title`,
ADD COLUMN `stock_option_type` ENUM('variant','bundle') NOT NULL DEFAULT 'variant' AFTER `keywords`,
ADD COLUMN `valid_from` TIMESTAMP NULL DEFAULT NULL AFTER `purchases`,
ADD COLUMN `valid_to` TIMESTAMP NULL DEFAULT NULL AFTER `valid_from`,
DROP INDEX `manufacturer_id`,
DROP INDEX `date_valid_from`,
DROP INDEX `date_valid_to`,
ADD INDEX `featured` (`featured`),
ADD INDEX `brand_id` (`brand_id`),
ADD INDEX `synonyms` (`synonyms`),
ADD INDEX `valid_from` (`valid_from`),
ADD INDEX `valid_to` (`valid_to`),
ADD FULLTEXT INDEX `name` (`name`),
ADD FULLTEXT INDEX `short_description` (`short_description`),
ADD FULLTEXT INDEX `description` (`description`);
-- -----
ALTER TABLE `lc_products_attributes`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `product_id` `product_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `group_id` `group_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `value_id` `value_id` INT(10) UNSIGNED NULL,
CHANGE COLUMN `custom_value` `custom_value` VARCHAR(215) NULL,
ADD COLUMN `priority` INT NOT NULL DEFAULT '0' AFTER `custom_value`;
-- -----
ALTER TABLE `lc_products_campaigns`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `product_id` `product_id` INT(10) UNSIGNED NOT NULL;
-- -----
ALTER TABLE `lc_products_customizations_values`
CHANGE COLUMN `price_operator` `price_modifier` CHAR(1) NOT NULL DEFAULT '+',
ADD COLUMN `price_adjustment` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0' AFTER `price_modifier`;
-- -----
ALTER TABLE `lc_products_images`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `product_id` `product_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `checksum` `checksum` CHAR(32) NOT NULL DEFAULT '';
-- -----
ALTER TABLE `lc_products_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `product_id` `product_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL;
-- -----
ALTER TABLE `lc_products_prices`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
CHANGE COLUMN `product_id` `product_id` INT(10) UNSIGNED NOT NULL AFTER `id`,
ADD COLUMN `customer_group_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `product_id`,
ADD COLUMN `min_quantity` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '1' AFTER `customer_group_id`,
ADD COLUMN `price` VARCHAR(512) NOT NULL DEFAULT '{}' AFTER `min_quantity`,
ADD INDEX `customer_group_id` (`customer_group_id`),
ADD INDEX `min_quantity` (`min_quantity`);
-- -----
ALTER TABLE `lc_products_stock_options`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `product_id` `product_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `combination` `attributes` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `sku` `sku` VARCHAR(32) NOT NULL DEFAULT '',
CHANGE COLUMN `weight` `weight` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `weight_class` `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
CHANGE COLUMN `dim_x` `length` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_y` `width` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_z` `height` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `dim_class` `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
ADD COLUMN `stock_item_id` INT(10) UNSIGNED NULL AFTER `product_id`,
ADD COLUMN `price_modifier` ENUM('+','%','*','=') NOT NULL DEFAULT '+' AFTER `length_unit`,
ADD COLUMN `price_adjustment` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0' AFTER `price_modifier`,
ADD UNIQUE KEY `product_stock_option` (`product_id`, `attributes`),
ADD INDEX `sku` (`sku`);
-- -----
ALTER TABLE `lc_products_to_categories`
CHANGE COLUMN `product_id` `product_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `category_id` `category_id` INT(10) UNSIGNED NULL;
-- -----
ALTER TABLE `lc_quantity_units`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `decimals` `decimals` TINYINT(1) UNSIGNED NOT NULL DEFAULT '2',
CHANGE COLUMN `separate` `separate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `name` TEXT NOT NULL DEFAULT '{}' AFTER `id`,
ADD COLUMN `description` TEXT NOT NULL DEFAULT '{}' AFTER `name`;
-- -----
ALTER TABLE `lc_quantity_units_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `quantity_unit_id` `quantity_unit_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL;
-- -----
ALTER TABLE `lc_settings`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `setting_group_key` `group_key` VARCHAR(64) NULL,
CHANGE COLUMN `key` `key` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE COLUMN `description` `description` VARCHAR(255) NOT NULL DEFAULT '',
CHANGE COLUMN `value` `value` VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN `required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `function`,
DROP COLUMN `type`,
DROP INDEX `setting_group_key`,
ADD INDEX `group_key` (`group_key`)
-- -----
ALTER TABLE `lc_settings_groups`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `key` `key` VARCHAR(32) NOT NULL;
-- -----
ALTER TABLE `lc_sold_out_statuses`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `hidden` `hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `orderable` `orderable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `name` TEXT NOT NULL DEFAULT '{}' AFTER `orderable`,
ADD COLUMN `description` TEXT NOT NULL DEFAULT '{}' AFTER `name`;
-- -----
ALTER TABLE `lc_sold_out_statuses_info`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `sold_out_status_id` `sold_out_status_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `language_code` `language_code` CHAR(2) NOT NULL;
-- -----
ALTER TABLE `lc_suppliers`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
-- -----
ALTER TABLE `lc_tax_classes`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
-- -----
ALTER TABLE `lc_tax_rates`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `tax_class_id` `tax_class_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `geo_zone_id` `geo_zone_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `rate` `rate` FLOAT(4,2) NOT NULL DEFAULT '0',
DROP COLUMN `type`;
-- -----
ALTER TABLE `lc_tax_rates`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `tax_class_id` `tax_class_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `geo_zone_id` `geo_zone_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `rate` `rate` FLOAT(6,4) UNSIGNED NOT NULL DEFAULT '0.0000',
CHANGE COLUMN `rule_companies_with_tax_id` `rule_companies_with_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `rule_companies_without_tax_id` `rule_companies_without_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `rule_individuals_with_tax_id` `rule_individuals_with_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `rule_individuals_without_tax_id` `rule_individuals_without_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_translations`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `code` `code` VARCHAR(128) NOT NULL DEFAULT '',
CHANGE COLUMN `html` `html` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `frontend` `frontend` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE COLUMN `backend` `backend` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_zones`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `country_code` `country_code` CHAR(2) NOT NULL,
CHANGE COLUMN `code` `code` VARCHAR(8) NOT NULL;
-- -----
ALTER TABLE `lc_zones_to_geo_zones`
CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `geo_zone_id` `geo_zone_id` INT(10) UNSIGNED NOT NULL,
CHANGE COLUMN `country_code` `country_code` CHAR(2) NOT NULL,
CHANGE COLUMN `zone_code` `zone_code` VARCHAR(8) NULL,
CHANGE COLUMN `city` `city` VARCHAR(32) NULL;
-- -----
INSERT IGNORE INTO `lc_banners`
(`id`, `status`, `name`, `languages`, `html`, `image`, `link`, `keywords`, `valid_from`, `valid_to`)
SELECT id, status, name, languages, '', replace(image, 'slides/', 'banners/'), '', 'jumbotron', date_valid_from, date_valid_to FROM `lc_slides`;
-- -----
INSERT INTO `lc_banners` (`status`, `name`, `languages`, `html`, `image`, `link`, `keywords`, `total_views`, `total_clicks`, `valid_from`, `valid_to`, `updated_at`, `created_at`) VALUES
(0, 'Jumbotron', '', '', 'banners/leaderboard.svg', '', 'jumbotron', 0, 0, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(0, 'Left', '', '<div class="placeholder" data-aspect-ratio="2:1" style="background: ivory;">Left</div>', '', '', 'left', 0, 0, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(0, 'Middle', '', '<div class="placeholder" data-aspect-ratio="2:1" style="background: ivory;">Middle</div>', '', '', 'middle', 0, 0, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(0, 'Right', '', '<div class="placeholder" data-aspect-ratio="2:1" style="background: seashell;">Right</div>', '', '', 'right', 0, 0, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_customer_groups` (`id`, `type`, `name`, `description`, `updated_at`, `created_at`)
VALUES (NULL, 'retail', 'Default', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_orders_items` (order_id, sku, name, quantity, price, tax_rate)
SELECT order_id, module_id, `title`, 1, amount, ROUND(tax / `amount` * 100, 2) from `lc_orders_totals`
WHERE calculate
ORDER BY order_id, priority;
-- -----
INSERT INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES
('social_media', 'Social Media', 'Social media related settings.', 30);
-- -----
INSERT INTO `lc_settings` (`group_key`, `title`, `description`, `key`, `value`, `function`, `required`, `priority`, `date_created`, `date_updated`) VALUES
('defaults', 'Default Order Status', 'Default order status for new orders if nothing else is set.', 'default_order_status_id', '1', 'order_status()', 0, 20, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('defaults', 'Default Address Format', 'The default address format if not specified otherwise.', 'default_address_format', '%company\n%firstname %lastname\n%address1\n%address2\n%country_code-%postcode %city\n%country_name', 'textarea()', 1, 14, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('customer_details', 'Different Shipping Address', 'Allow customers to provide a different address for shipping.', 'customer_shipping_address', '1', 'toggle("y/n")', 0, 24, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('listings', 'Featured Products Box: Number of Items', 'The maximum number of items to be displayed in the box.', 'box_featured_products_num_items', '10', 'number()', 0, 17, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('checkout', 'Order Number Format', 'Specify the format for creating order numbers. {id} = order id, {yy} = year, {mm} = month, {q} = quarter, {l} length digit, {#} = luhn checksum digit', 'order_no_format', '{id}', 'text()', 1, 20, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('advanced', 'Static Content Domain Name', 'Use the given alias domain name for static content (fonts, images, stylesheets, javascripts, etc.).', 'static_domain', '', 'text()', 0, 12, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('social_media', 'Facebook Link', 'The link to your Facebook page.', 'facebook_link', '', 'url()', 0, 10, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('social_media', 'Instagram Link', 'The link to your Instagram page.', 'instagram_link', '', 'url()', 0, 20, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('social_media', 'LinkedIn Link', 'The link to your LinkedIn page.', 'linkedin_link', '', 'url()', 0, 30, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('social_media', 'Pinterest Link', 'The link to your Pinterest page.', 'pinterest_link', '', 'url()', 0, 40, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('social_media', 'X Link', 'The link to your X page.', 'x_link', '', 'url()', 0, 50, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('social_media', 'YouTube Link', 'The link to your YouTube channel.', 'youtube_link', '', 'url()', 0, 60, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
UPDATE `lc_attribute_groups`
SET name = '{}';
-- -----
UPDATE `lc_attribute_values`
SET name = '{}';
-- -----
UPDATE `lc_brands`
SET image = REPLACE(image, 'manufacturers/', 'brands/'),
	short_description = '{}',
	description = '{}',
	h1_title = '{}',
	head_title = '{}',
	meta_description = '{}',
	link = '{}';
-- -----
UPDATE `lc_cart_items`
SET customer_id = NULL
WHERE customer_id = 0;
-- -----
UPDATE `lc_categories`
SET parent_id = NULL
WHERE parent_id = 0;
-- -----
UPDATE `lc_categories`
SET name = '{}',
	short_description = '{}',
	description = '{}',
	synonyms = '{}',
	head_title = '{}',
	h1_title = '{}',
	meta_description = '{}';
-- -----
UPDATE `lc_delivery_statuses`
SET name = '{}',
	description = '{}';
-- -----
UPDATE `lc_modules`
SET `settings` = REPLACE(settings, 'weight_class', 'weight_unit')
WHERE `module_id` = 'sm_zone_weight'
LIMIT 1;
-- -----
UPDATE `lc_modules`
SET `module_id` = 'job_cleaner'
WHERE `module_id` = 'job_cache_cleaner'
LIMIT 1;
-- -----
UPDATE `lc_newsletter_recipients` nr
LEFT JOIN `lc_customers` c ON (nr.email = c.email)
SET nr.country_code = c.country_code;
-- -----
UPDATE `lc_orders`
SET order_status_id = NULL
WHERE order_status_id = 0;
-- -----
UPDATE `lc_orders`
SET customer_id = NULL
WHERE customer_id = 0;
-- -----
UPDATE `lc_orders`
SET language_code = NULL
WHERE language_code = '';
-- -----
UPDATE `lc_orders`
SET `no` = id;
-- -----
UPDATE `lc_orders`
SET shipping_tax_id = customer_tax_id,
	shipping_phone = customer_phone,
	shipping_email = customer_email;
-- -----
UPDATE `lc_orders` o
LEFT JOIN `lc_orders_totals` ot ON (ot.order_id = o.id AND ot.module_id = 'ot_subtotal')
SET o.subtotal = ot.`amount`,
o.subtotal_tax = ot.`tax`;
-- -----
UPDATE `lc_orders` o
LEFT JOIN `lc_orders_totals` ot ON (ot.order_id = o.id AND ot.module_id = 'ot_shipping_fee')
SET o.shipping_option_fee = ot.`amount`,
o.shipping_option_tax = ot.`tax`;
-- -----
UPDATE `lc_orders` o
LEFT JOIN `lc_orders_totals` ot ON (ot.order_id = o.id AND ot.module_id = 'ot_payment_fee')
SET o.payment_option_fee = ot.`amount`,
o.payment_option_tax = ot.`tax`;
-- -----
DELETE FROM `lc_orders_totals`
WHERE `module_id` IN ('ot_subtotal', 'ot_shipping_fee', 'ot_payment_fee');
-- -----
UPDATE `lc_orders` o
LEFT JOIN (
	SELECT order_id, sum(`amount`) as discount, sum(`tax`) as discount_tax
	FROM `lc_orders_totals`
	WHERE `amount` < 0
AND calculate
	GROUP BY order_id
) ot ON (ot.order_id = o.id)
SET o.discount = 0 - if(ot.discount, ot.discount, 0),
o.discount_tax = 0 - if(ot.discount_tax, ot.discount_tax, 0);
-- -----
UPDATE `lc_order_statuses`
SET name = '{}',
	description = '{}',
	email_subject = '{}',
	email_message = '{}';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-money-bill' WHERE icon = 'fa-money';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-clock' WHERE icon = 'fa-clock-o';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-cog' WHERE icon = 'fa-cog';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-home' WHERE icon = 'fa-home';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-truck' WHERE icon = 'fa-truck';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-times' WHERE icon = 'fa-times';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-plus' WHERE icon = 'fa-plus';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-pause' WHERE icon = 'fa-pause';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-hourglass-half' WHERE icon = 'fa-hourglass-half';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-undo' WHERE icon = 'fa-undo';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-building' WHERE icon = 'fa-building';
-- -----
UPDATE `lc_order_statuses` SET icon = 'icon-exclamation' WHERE icon = 'fa-exclamation';
-- -----
UPDATE `lc_orders_items` oi
LEFT JOIN `lc_orders` o ON (o.id = oi.order_id)
LEFT JOIN `lc_products` p ON (p.id = oi.product_id)
SET oi.tax_class_id = p.tax_class_id,
	oi.discount = oi.price * (o.discount/o.total),
	oi.discount_tax = oi.price * (o.discount_tax/o.total),
	oi.`sum` = oi.price - (oi.price * (o.discount/o.total)),
	oi.sum_tax = oi.tax - (oi.tax * (o.discount/o.total));
-- -----
UPDATE `lc_orders_items`
SET sum = price * quantity,
	sum_tax = tax * quantity;
-- -----
UPDATE `lc_products_to_categories`
SET `category_id` = NULL
WHERE `category_id` = 0;
-- -----
UPDATE `lc_pages`
SET dock = REPLACE(dock, 'customer_service', 'information'),
	title = '{}',
	content = '{}',
	head_title = '{}',
	meta_description = '{}';
-- -----
UPDATE `lc_pages`
SET parent_id = NULL
WHERE parent_id = 0;
-- -----
UPDATE `lc_products`
SET name = '{}',
	short_description = '{}',
	description = '{}',
	technical_data = '{}',
	synonyms = '{}',
	head_title = '{}',
	meta_description = '{}';
-- -----
UPDATE `lc_quantity_units`
SET name = '{}',
	description = '{}';
-- -----
UPDATE `lc_settings`
SET `value` = ''
WHERE `value` = 'https://'
and `key` IN ('control_panel_link', 'database_admin_link', 'webmail_link');
-- -----
UPDATE `lc_settings`
SET `value` = '0'
WHERE `key` = 'cache_clear_thumbnails'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `key` = 'template',
	`title` = 'Template',
	`value` = REGEXP_REPLACE(`value`, '\.catalog$', '')
WHERE `key` = 'store_template_catalog'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `key` = 'template_settings',
	`title` = 'Template Settings'
WHERE `key` = 'store_template_catalog_settings';
-- -----
UPDATE `lc_settings`
SET `key` = 'store_weight_unit',
	`title` = 'Store Weight Unit',
	`description` = 'The prefered weight unit.'
WHERE `key` = 'store_length_class'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `key` = 'store_length_unit',
`title` = 'Store Length Unit',
`description` = 'The prefered length unit.'
WHERE `key` = 'store_weight_class'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `required` = 1
WHERE `key` IN (
	'store_email', 'store_name', 'store_language_code','store_currency_code', 'store_weight_unit', 'store_length_unit', 'store_timezone',
	'default_language_code', 'default_currency_code', 'default_country_code', 'default_zone_code', 'template'
);
-- -----
UPDATE `lc_settings`
SET `function` = 'select("FIT","CROP")'
WHERE `key` = 'category_image_clipping'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `title` = 'X Link',
	`description` = 'The link to your X page.',
	`key` = 'x_link'
WHERE `key` = 'twitter_link';
-- -----
UPDATE `lc_settings`
SET `value` = 'FIT'
WHERE `key` = 'category_image_clipping'
AND `value` IN ('FIT_USE_WHITESPACING', 'FIT_ONLY_BIGGER', 'FIT_ONLY_BIGGER_USE_WHITESPACING')
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `function` = 'select("FIT","CROP")'
WHERE `key` = 'product_image_clipping'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `value` = 'FIT'
WHERE `key` = 'product_image_clipping'
AND `value` IN ('FIT_USE_WHITESPACING', 'FIT_ONLY_BIGGER', 'FIT_ONLY_BIGGER_USE_WHITESPACING')
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `function` = 'regional_text()'
WHERE `function` = 'regional_input()';
-- -----
UPDATE `lc_settings`
SET `key` = 'jobs_last_push',
	`title` = 'Background Jobs Last Push',
	`description` = 'Time when background jobs were last pushed.'
WHERE `key` = 'jobs_last_push'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `value` = ''
WHERE `value` IN ('?app=settings&doc=advanced&action=edit&key=control_panel_link', '?app=settings&doc=advanced&action=edit&key=database_admin_link', '?app=settings&doc=advanced&action=edit&key=webmail_link');
-- -----
UPDATE `lc_settings`
SET `value` = 1
WHERE `key` = 'maintenance_mode'
AND EXISTS (
	SELECT 1 FROM `lc_settings`
	WHERE `key` = 'development_mode'
	AND `value` = 1
);
-- -----
UPDATE `lc_sold_out_statuses`
SET name = '{}',
	description = '{}';
-- -----
UPDATE `lc_zones_to_geo_zones`
SET `zone_code` = NULL
WHERE `zone_code` = '';
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
DELETE FROM `lc_settings`
WHERE `key` IN ('auto_decimals', 'cache_system_breakpoint', 'development_mode', 'jobs_interval', 'round_amounts', 'store_template_admin', 'store_template_admin_settings');
-- -----
DELETE FROM `lc_modules`
WHERE `module_id` = 'ot_subtotal'
LIMIT 1;
-- -----
DELETE FROM `lc_attribute_groups_info`
WHERE group_id NOT IN (SELECT id from `lc_attribute_groups`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_attribute_values`
WHERE group_id NOT IN (SELECT id from `lc_attribute_groups`);
-- -----
DELETE FROM `lc_attribute_values_info`
WHERE value_id NOT IN (SELECT id from `lc_attribute_values`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_brands_info`
WHERE brand_id NOT IN (SELECT id from `lc_brands`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_cart_items`
WHERE customer_id != 0
AND customer_id NOT IN (
	SELECT id from `lc_customers`
);
-- -----
DELETE FROM `lc_cart_items`
WHERE product_id NOT IN (
	SELECT id from `lc_products`
);
-- -----
UPDATE `lc_customers`
SET `group_id` = NULL
WHERE `group_id` NOT IN (
	SELECT id from `lc_customer_groups`
);
-- -----
DELETE FROM `lc_categories_info`
WHERE category_id NOT IN (
	SELECT code from `lc_categories`
)
OR language_code NOT IN (
	SELECT code from `lc_languages`
);
-- -----
DELETE FROM `lc_categories_filters`
WHERE category_id NOT IN (SELECT id from `lc_categories`)
OR attribute_group_id NOT IN (SELECT id from `lc_attribute_groups`);
-- -----
DELETE FROM `lc_categories_info`
WHERE category_id NOT IN (SELECT id from `lc_categories`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_delivery_statuses_info`
WHERE delivery_status_id NOT IN (SELECT id from `lc_delivery_statuses`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_orders_comments`
WHERE order_id NOT IN (SELECT id from `lc_orders`);
-- -----
DELETE FROM `lc_orders_items`
WHERE order_id NOT IN (SELECT id from `lc_orders`);
-- -----
UPDATE `lc_orders_items`
SET product_id = NULL
WHERE product_id NOT IN (SELECT id from `lc_products`);
-- -----
DELETE FROM `lc_order_statuses_info`
WHERE order_status_id NOT IN (SELECT id from `lc_order_statuses`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_pages_info`
WHERE page_id NOT IN (SELECT id from `lc_pages`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_products_attributes`
WHERE product_id NOT IN (SELECT id from `lc_products`)
OR group_id NOT IN (SELECT id from `lc_attribute_groups`)
OR (
	value_id IS NOT NULL
	AND value_id != 0
	AND value_id NOT IN (SELECT id from `lc_attribute_values`)
);
-- -----
DELETE FROM `lc_products_customizations`
WHERE product_id NOT IN (SELECT id from `lc_products`)
OR group_id NOT IN (SELECT id from `lc_attribute_groups`);
-- -----
DELETE FROM `lc_products_customizations_values`
WHERE id NOT IN (SELECT id from `lc_products_customizations`)
OR product_id NOT IN (SELECT id from `lc_products`)
OR group_id NOT IN (SELECT id from `lc_attribute_groups`)
OR value_id NOT IN (SELECT id from `lc_attribute_values`);
-- -----
DELETE FROM `lc_products_campaigns`
WHERE product_id NOT IN (SELECT id from `lc_products`);
-- -----
DELETE FROM `lc_products_images`
WHERE product_id NOT IN (SELECT id from `lc_products`);
-- -----
DELETE FROM `lc_products_info`
WHERE product_id NOT IN (SELECT id from `lc_products`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE pp1
FROM `lc_products_prices` pp1
JOIN `lc_products_prices` pp2
WHERE (
	pp1.product_id = pp2.product_id
	AND pp1.customer_group_id = pp2.customer_group_id
	AND pp1.min_quantity = pp2.min_quantity
	AND pp1.id > pp2.id
);
-- -----
DELETE FROM `lc_products_prices`
WHERE product_id NOT IN (SELECT id from `lc_products`);
-- -----
DELETE FROM `lc_products_to_categories`
WHERE product_id NOT IN (SELECT id from `lc_products`)
OR category_id NOT IN (SELECT id from `lc_categories`);
-- -----
DELETE FROM `lc_quantity_units_info`
WHERE quantity_unit_id NOT IN (SELECT id from `lc_quantity_units`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_settings`
WHERE `key` IN ('gzip_enabled');
-- -----
DELETE FROM `lc_sold_out_statuses_info`
WHERE sold_out_status_id NOT IN (SELECT id from `lc_sold_out_statuses`)
OR language_code NOT IN (SELECT code from `lc_languages`);
-- -----
DELETE FROM `lc_tax_rates`
WHERE tax_class_id NOT IN (SELECT id from `lc_tax_classes`)
OR geo_zone_id NOT IN (SELECT id from `lc_geo_zones`);
-- -----
DELETE FROM `lc_zones`
WHERE country_code NOT IN (SELECT iso_code_2 from `lc_countries`);
-- -----
DELETE FROM `lc_zones_to_geo_zones`
WHERE geo_zone_id NOT IN (SELECT id from `lc_geo_zones`)
OR country_code NOT IN (SELECT iso_code_2 from `lc_countries`);
-- -----
DROP TABLE `lc_orders_totals`;
-- -----
DROP TABLE `lc_slides`;
-- -----
DROP TABLE `lc_slides_info`;
