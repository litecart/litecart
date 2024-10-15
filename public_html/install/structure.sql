CREATE TABLE `lc_administrators` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`username` VARCHAR(32) NOT NULL DEFAULT '',
	`email` VARCHAR(128) NOT NULL DEFAULT '',
	`password_hash` VARCHAR(255) NOT NULL DEFAULT '',
	`apps` VARCHAR(4096) NOT NULL DEFAULT '',
	`widgets` VARCHAR(512) NOT NULL DEFAULT '',
	`two_factor_authentication` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`login_attempts` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`total_logins` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`last_ip_address` VARCHAR(39) NOT NULL DEFAULT '',
	`last_hostname` VARCHAR(128) NOT NULL DEFAULT '',
	`last_user_agent` VARCHAR(255) NOT NULL DEFAULT '',
	`known_ips` VARCHAR(512) NOT NULL DEFAULT '',
	`date_valid_from` TIMESTAMP NULL DEFAULT NULL,
	`date_valid_to` TIMESTAMP NULL DEFAULT NULL,
	`date_active` TIMESTAMP NULL DEFAULT NULL,
	`date_login` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `status` (`status`),
	KEY `username` (`username`),
	KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_attribute_groups` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`code` VARCHAR(32) NOT NULL DEFAULT '',
	`sort` ENUM('alphabetical','priority') NOT NULL DEFAULT 'alphabetical',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_attribute_groups_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`group_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `attribute_group` (`group_id`,`language_code`),
	KEY `group_id` (`group_id`),
	KEY `language_code` (`language_code`),
	CONSTRAINT `attribute_group_info_to_attribute_group` FOREIGN KEY (`group_id`) REFERENCES `lc_attribute_groups` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `attribute_group_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_attribute_values` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`group_id` INT(10) UNSIGNED NOT NULL,
	`priority` INT(11) NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_attribute_values_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`value_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `attribute_value` (`value_id`,`language_code`),
	KEY `value_id` (`value_id`),
	KEY `language_code` (`language_code`),
	CONSTRAINT `attribute_value_info to attribute_value` FOREIGN KEY (`value_id`) REFERENCES `lc_attribute_values` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `attribute_value_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
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
	`date_valid_from` TIMESTAMP NULL DEFAULT NULL,
	`date_valid_to` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_brands` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`featured` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`code` VARCHAR(32) NOT NULL DEFAULT '',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`keywords` VARCHAR(255) NOT NULL DEFAULT '',
	`image` VARCHAR(255) NOT NULL DEFAULT '',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `code` (`code`),
	KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_brands_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`brand_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`short_description` VARCHAR(255) NOT NULL DEFAULT '',
	`description` TEXT NOT NULL DEFAULT '',
	`h1_title` VARCHAR(128) NOT NULL DEFAULT '',
	`head_title` VARCHAR(128) NOT NULL DEFAULT '',
	`meta_description` VARCHAR(512) NOT NULL DEFAULT '',
	`link` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `brand_info` (`brand_id`, `language_code`),
	KEY `brand_id` (`brand_id`),
	KEY `language_code` (`language_code`),
	CONSTRAINT `brand` FOREIGN KEY (`brand_id`) REFERENCES `lc_brands` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `brand_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_campaigns` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`name` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8mb4_swedish_ci',
	`date_valid_from` TIMESTAMP NULL DEFAULT NULL,
	`date_valid_to` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `date_valid_from` (`date_valid_from`) USING BTREE,
	INDEX `date_valid_to` (`date_valid_to`) USING BTREE,
	INDEX `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_campaigns_products` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`campaign_id` INT(10) UNSIGNED NOT NULL,
	`product_id` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `product_id` (`product_id`) USING BTREE,
	INDEX `campaign_id` (`campaign_id`) USING BTREE,
	CONSTRAINT `campaign_price_to_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `lc_campaigns` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `campaign_price_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_cart_items` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_id` INT(10) UNSIGNED NULL,
	`cart_uid` VARCHAR(13) NOT NULL DEFAULT '',
	`key` VARCHAR(32) NOT NULL DEFAULT '',
	`product_id` INT(10) UNSIGNED NULL,
	`stock_option_id` INT(10) UNSIGNED NULL,
	`userdata` VARCHAR(2048) NOT NULL DEFAULT '',
	`image` VARCHAR(255) NOT NULL DEFAULT '',
	`quantity` FLOAT(11, 4) NOT NULL DEFAULT '1',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `customer_id` (`customer_id`),
	KEY `cart_uid` (`cart_uid`),
	CONSTRAINT `cart_item_to_customer` FOREIGN KEY (`customer_id`) REFERENCES `lc_customer` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `cart_item_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- -----
CREATE TABLE `lc_categories` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`parent_id` INT(10) UNSIGNED NULL,
	`google_taxonomy_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`code` VARCHAR(64) NOT NULL DEFAULT '',
	`keywords` VARCHAR(255) NOT NULL DEFAULT '',
	`image` VARCHAR(255) NOT NULL DEFAULT '',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),
	KEY `code` (`code`),
	KEY `parent_id` (`parent_id`),
	KEY `status` (`status`),
	CONSTRAINT `category_to_parent` FOREIGN KEY (`parent_id`) REFERENCES `lc_categories` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_categories_filters` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`category_id` INT(10) UNSIGNED NOT NULL,
	`attribute_group_id` INT(10) UNSIGNED NOT NULL,
	`select_multiple` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `attribute_filter` (`category_id`, `attribute_group_id`),
	KEY `category_id` (`category_id`),
	CONSTRAINT `category_filter_to_category` FOREIGN KEY (`category_id`) REFERENCES `lc_categories` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `category_filter_to_attribute_group` FOREIGN KEY (`attribute_group_id`) REFERENCES `lc_attribute_groups` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_categories_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`category_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '',
	`description` TEXT NOT NULL DEFAULT '',
	`synonyms` VARCHAR(256) NOT NULL DEFAULT '',
	`head_title` VARCHAR(128) NOT NULL DEFAULT '',
	`h1_title` VARCHAR(128) NOT NULL DEFAULT '',
	`meta_description` VARCHAR(512) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `category` (`category_id`, `language_code`),
	KEY `category_id` (`category_id`),
	KEY `language_code` (`language_code`),
	FULLTEXT INDEX `name` (`name`),
	FULLTEXT INDEX `short_description` (`short_description`),
	FULLTEXT INDEX `description` (`description`),
	FULLTEXT INDEX `synonyms` (`synonyms`);
	CONSTRAINT `category_info_to_category` FOREIGN KEY (`category_id`) REFERENCES `lc_categories` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `category_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_countries` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`iso_code_1` CHAR(3) NOT NULL DEFAULT '',
	`iso_code_2` CHAR(2) NOT NULL DEFAULT '',
	`iso_code_3` CHAR(3) NOT NULL DEFAULT '',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`domestic_name` VARCHAR(64) NOT NULL DEFAULT '',
	`tax_id_format` VARCHAR(64) NOT NULL DEFAULT '',
	`address_format` VARCHAR(128) NOT NULL DEFAULT '',
	`postcode_format` VARCHAR(255) NOT NULL DEFAULT '',
	`postcode_required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`language_code` CHAR(2) NOT NULL,
	`currency_code` CHAR(3) NOT NULL DEFAULT '',
	`phone_code` VARCHAR(3) NOT NULL DEFAULT '',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `iso_code_1` (`iso_code_1`),
	UNIQUE KEY `iso_code_2` (`iso_code_2`),
	UNIQUE KEY `iso_code_3` (`iso_code_3`),
	KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_currencies` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) NOT NULL DEFAULT '0',
	`code` CHAR(3) NOT NULL DEFAULT '',
	`number` CHAR(3) NOT NULL DEFAULT '',
	`name` VARCHAR(32) NOT NULL DEFAULT '',
	`value` FLOAT(11,6) UNSIGNED NOT NULL DEFAULT '1.000000',
	`decimals` TINYINT(1) UNSIGNED NOT NULL DEFAULT '2',
	`prefix` VARCHAR(8) NOT NULL DEFAULT '',
	`suffix` VARCHAR(8) NOT NULL DEFAULT '',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `status` (`status`),
	KEY `code` (`code`),
	KEY `number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_customers` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`code` VARCHAR(32) NOT NULL DEFAULT '',
	`email` VARCHAR(128) NOT NULL DEFAULT '',
	`password_hash` VARCHAR(255) NOT NULL DEFAULT '',
	`type` ENUM('','business','individual') NOT NULL DEFAULT '';
	`tax_id` VARCHAR(32) NOT NULL DEFAULT '',
	`company` VARCHAR(64) NOT NULL DEFAULT '',
	`firstname` VARCHAR(64) NOT NULL DEFAULT '',
	`lastname` VARCHAR(64) NOT NULL DEFAULT '',
	`address1` VARCHAR(64) NOT NULL DEFAULT '',
	`address2` VARCHAR(64) NOT NULL DEFAULT '',
	`postcode` VARCHAR(16) NOT NULL DEFAULT '',
	`city` VARCHAR(32) NOT NULL DEFAULT '',
	`country_code` CHAR(2) NOT NULL DEFAULT '',
	`zone_code` VARCHAR(8) NOT NULL DEFAULT '',
	`phone` VARCHAR(24) NOT NULL DEFAULT '',
	`default_billing_address_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`default_shipping_address_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`notes` TEXT NOT NULL DEFAULT '',
	`password_reset_token` VARCHAR(128) NOT NULL DEFAULT '',
	`login_attempts` INT(11) NOT NULL DEFAULT '0',
	`total_logins` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`last_ip` VARCHAR(39) NOT NULL DEFAULT '',
	`last_host` VARCHAR(128) NOT NULL DEFAULT '',
	`last_agent` VARCHAR(255) NOT NULL DEFAULT '',
	`date_login` TIMESTAMP NULL DEFAULT NULL,
	`date_blocked_until` TIMESTAMP NULL DEFAULT NULL,
	`date_expire_sessions` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_customers_addresses` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_id` INT(10) UNSIGNED NOT NULL,
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
	INDEX `customer_id` (`customer_id`) USING BTREE,
	CONSTRAINT `customer_address_to_customer` FOREIGN KEY (`customer_id`) REFERENCES `lc_customers` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_delivery_statuses` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_delivery_statuses_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`delivery_status_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`description` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `delivery_status` (`delivery_status_id`, `language_code`),
	KEY `delivery_status_id` (`delivery_status_id`),
	KEY `language_code` (`language_code`),
	CONSTRAINT `delivery_status_info_to_delivery_status` FOREIGN KEY (`delivery_status_id`) REFERENCES `lc_delivery_statuses` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `delivery_status_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_emails` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` ENUM('draft','scheduled','sent','error') NOT NULL DEFAULT 'draft',
	`code` VARCHAR(255) NOT NULL DEFAULT '',
	`sender` VARCHAR(255) NOT NULL DEFAULT '',
	`recipients` TEXT NOT NULL DEFAULT '',
	`ccs` TEXT NOT NULL DEFAULT '',
	`bccs` TEXT NOT NULL DEFAULT '',
	`subject` VARCHAR(255) NOT NULL DEFAULT '',
	`multiparts` MEDIUMTEXT NOT NULL DEFAULT '',
	`date_scheduled` TIMESTAMP NULL DEFAULT NULL,
	`date_sent` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `date_scheduled` (`date_scheduled`),
	KEY `code` (`code`),
	KEY `date_created` (`date_created`),
	KEY `sender_email` (`sender`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_geo_zones` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`code` VARCHAR(32) NOT NULL DEFAULT '',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`description` VARCHAR(255) NOT NULL DEFAULT '',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_languages` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) NOT NULL DEFAULT '0',
	`code` CHAR(2) NOT NULL DEFAULT '',
	`code2` CHAR(3) NOT NULL DEFAULT '',
	`name` VARCHAR(32) NOT NULL DEFAULT '',
	`direction` ENUM('ltr','rtl') NOT NULL DEFAULT 'ltr',
	`locale` VARCHAR(64) NOT NULL DEFAULT '',
	`url_type` VARCHAR(16) NOT NULL DEFAULT '',
	`domain_name` VARCHAR(64) NOT NULL DEFAULT '',
	`raw_date` VARCHAR(32) NOT NULL DEFAULT '',
	`raw_time` VARCHAR(32) NOT NULL DEFAULT '',
	`raw_datetime` VARCHAR(32) NOT NULL DEFAULT '',
	`format_date` VARCHAR(32) NOT NULL DEFAULT '',
	`format_time` VARCHAR(32) NOT NULL DEFAULT '',
	`format_datetime` VARCHAR(32) NOT NULL DEFAULT '',
	`decimal_point` VARCHAR(1) NOT NULL DEFAULT '',
	`thousands_sep` VARCHAR(1) NOT NULL DEFAULT '',
	`currency_code` CHAR(3) NOT NULL DEFAULT '',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY `id` (`id`),
	KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_modules` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`module_id` VARCHAR(64) NOT NULL DEFAULT '',
	`type` VARCHAR(16) NOT NULL DEFAULT '',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`settings` TEXT NOT NULL DEFAULT '',
	`last_log` TEXT NOT NULL DEFAULT '',
	`date_pushed` TIMESTAMP NULL DEFAULT NULL,
	`date_processed` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `module_id` (`module_id`),
	KEY `type` (`type`),
	KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_newsletter_recipients` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`lastname` VARCHAR(64) NOT NULL DEFAULT '',
	`firstname` VARCHAR(64) NOT NULL DEFAULT '',
	`email` VARCHAR(128) NOT NULL DEFAULT '',
	`ip_address` VARCHAR(64) NOT NULL DEFAULT '',
	`hostname` VARCHAR(128) NOT NULL DEFAULT '',
	`user_agent` VARCHAR(256) NOT NULL DEFAULT '',
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_orders` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`no` VARCHAR(1) NOT NULL DEFAULT '',
	`starred` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`unread` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`order_status_id` INT(10) UNSIGNED NULL,
	`customer_id` INT(10) UNSIGNED NULL,
	`billing_email` VARCHAR(128) NOT NULL DEFAULT '',
	`billing_tax_id` VARCHAR(32) NOT NULL DEFAULT '',
	`billing_company` VARCHAR(64) NOT NULL DEFAULT '',
	`billing_firstname` VARCHAR(64) NOT NULL DEFAULT '',
	`billing_lastname` VARCHAR(64) NOT NULL DEFAULT '',
	`billing_address1` VARCHAR(64) NOT NULL DEFAULT '',
	`billing_address2` VARCHAR(64) NOT NULL DEFAULT '',
	`billing_city` VARCHAR(32) NOT NULL DEFAULT '',
	`billing_postcode` VARCHAR(16) NOT NULL DEFAULT '',
	`billing_country_code` CHAR(2) NOT NULL DEFAULT '',
	`billing_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
	`billing_phone` VARCHAR(24) NOT NULL DEFAULT '',
	`billing_email` VARCHAR(128) NOT NULL DEFAULT '',
	`shipping_tax_id` VARCHAR(64) NOT NULL DEFAULT '',
	`shipping_company` VARCHAR(64) NOT NULL DEFAULT '',
	`shipping_tax_id` VARCHAR(32) NOT NULL DEFAULT '',
	`shipping_firstname` VARCHAR(64) NOT NULL DEFAULT '',
	`shipping_lastname` VARCHAR(64) NOT NULL DEFAULT '',
	`shipping_address1` VARCHAR(64) NOT NULL DEFAULT '',
	`shipping_address2` VARCHAR(64) NOT NULL DEFAULT '',
	`shipping_city` VARCHAR(32) NOT NULL DEFAULT '',
	`shipping_postcode` VARCHAR(16) NOT NULL DEFAULT '',
	`shipping_country_code` CHAR(2) NOT NULL DEFAULT '',
	`shipping_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
	`shipping_phone` VARCHAR(24) NOT NULL DEFAULT '',
	`shipping_email` VARCHAR(128) NOT NULL DEFAULT '',
	`shipping_option_id` VARCHAR(32) NOT NULL DEFAULT '',
	`shipping_option_name` VARCHAR(64) NOT NULL DEFAULT '',
	`shipping_option_userdata` VARCHAR(512) NOT NULL DEFAULT '',
	`shipping_option_fee` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`shipping_option_tax` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`shipping_purchase_cost` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`shipping_tracking_id` VARCHAR(128) NOT NULL DEFAULT '',
	`shipping_tracking_url` VARCHAR(255) NOT NULL DEFAULT '',
	`shipping_progress` TINYINT(3) NOT NULL DEFAULT '0',
	`shipping_current_status` VARCHAR(64) NOT NULL DEFAULT '',
	`shipping_current_location` VARCHAR(128) NOT NULL DEFAULT '',
	`payment_option_id` VARCHAR(32) NOT NULL DEFAULT '',
	`payment_option_name` VARCHAR(64) NOT NULL DEFAULT '',
	`payment_option_userdata` VARCHAR(512) NOT NULL DEFAULT '',
	`payment_option_fee` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`payment_option_tax` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`payment_transaction_id` VARCHAR(128) NOT NULL DEFAULT '',
	`payment_transaction_fee` FLOAT(11,4) NOT NULL DEFAULT '',
	`payment_receipt_url` VARCHAR(255) NOT NULL DEFAULT '',
	`payment_terms` VARCHAR(8) NOT NULL DEFAULT '',
	`incoterm` VARCHAR(3) NOT NULL DEFAULT '',
	`reference` VARCHAR(128) NOT NULL DEFAULT '',
	`language_code` CHAR(2) NULL,
	`weight_total` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
	`currency_code` CHAR(3) NOT NULL DEFAULT '',
	`currency_value` FLOAT(11,6) UNSIGNED NOT NULL DEFAULT '0.000000',
	`display_prices_including_tax` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`subtotal` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`subtotal_tax` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`discount` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`discount_tax` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`total` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`total_tax` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`notes` VARCHAR(1024) NOT NULL DEFAULT '',
	`ip_address` VARCHAR(39) NOT NULL DEFAULT '',
	`hostname` VARCHAR(128) NOT NULL DEFAULT '',
	`user_agent` VARCHAR(255) NOT NULL DEFAULT '',
	`domain` VARCHAR(64) NOT NULL DEFAULT '',
	`public_key` VARCHAR(32) NOT NULL DEFAULT '',
	`date_paid` TIMESTAMP NULL DEFAULT NULL,
	`date_dispatched` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `no` (`no`),
	KEY `order_status_id` (`order_status_id`),
	KEY `starred` (`starred`),
	KEY `unread` (`unread`),
	CONSTRAINT `order_to_customer` FOREIGN KEY (`customer_id`) REFERENCES `lc_customers` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
	CONSTRAINT `order_to_order_status` FOREIGN KEY (`order_status_id`) REFERENCES `lc_order_statuses` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_orders_comments` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` INT(10) UNSIGNED NOT NULL,
	`author_id` INT(10) UNSIGNED NULL,
	`author` enum('system','staff','customer') NOT NULL DEFAULT 'system',
	`text` VARCHAR(512) NOT NULL DEFAULT '',
	`hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `order_id` (`order_id`),
	CONSTRAINT `order_comment_to_order` FOREIGN KEY (`order_id`) REFERENCES `lc_orders` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_orders_items` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` INT(10) UNSIGNED NOT NULL,
	`product_id` INT(10) UNSIGNED NULL,
	`stock_option_id` INT(10) UNSIGNED NULL,
	`userdata` VARCHAR(2048) UNSIGNED NULL,
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	`sku` VARCHAR(32) NOT NULL DEFAULT '',
	`gtin` VARCHAR(32) NOT NULL DEFAULT '',
	`taric` VARCHAR(32) NOT NULL DEFAULT '',
	`quantity` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`price` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`tax` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`tax_rate` FLOAT(4,2) NOT NULL DEFAULT '0.00',
	`discount` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`discount_tax` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`sum` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`sum_tax` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`weight` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
	`length` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`width` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`height` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`length_unit` VARCHAR(2) NOT NULL DEFAULT '',
	`downloads` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `order_id` (`order_id`),
	KEY `product_id` (`product_id`),
	CONSTRAINT `order_item_to_order` FOREIGN KEY (`order_id`) REFERENCES `lc_orders` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `order_item_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
	CONSTRAINT `order_item_to_stock_option` FOREIGN KEY (`stock_option_id`) REFERENCES `lc_products_stock_options` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_orders_totals` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` INT(10) UNSIGNED NOT NULL,
	`module_id` VARCHAR(32) NOT NULL DEFAULT '',
	`title` VARCHAR(128) NOT NULL DEFAULT '',
	`amount` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`tax` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`tax_rate` FLOAT(6,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`calculate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `order_id` (`order_id`),
	CONSTRAINT `order_total_to_order` FOREIGN KEY (`order_id`) REFERENCES `lc_orders` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_order_statuses` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`state` ENUM('','created','on_hold','ready','delayed','processing','completed','dispatched','in_transit','delivered','returning','returned','cancelled','fraud','other') NOT NULL DEFAULT '',
	`hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`icon` VARCHAR(24) NOT NULL DEFAULT '',
	`color` VARCHAR(7) NOT NULL DEFAULT '',
	`is_sale` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`is_archived` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`is_trackable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`stock_action` ENUM('none','reserve','commit') NOT NULL DEFAULT 'none',
	`notify` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `is_sale` (`is_sale`),
	KEY `is_archived` (`is_archived`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_order_statuses_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_status_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`description` VARCHAR(255) NOT NULL DEFAULT '',
	`email_subject` VARCHAR(128) NOT NULL DEFAULT '',
	`email_message` TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `order_status_info` (`order_status_id`, `language_code`),
	KEY `order_status_id` (`order_status_id`),
	KEY `language_code` (`language_code`),
	CONSTRAINT `order_status_info_to_order` FOREIGN KEY (`order_status_id`) REFERENCES `lc_order_statuses` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `order_status_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_pages` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`parent_id` INT(10) UNSIGNED NULL,
	`status` TINYINT(1) NOT NULL DEFAULT '0',
	`dock` VARCHAR(64) NOT NULL DEFAULT '',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `status` (`status`),
	KEY `parent_id` (`parent_id`),
	KEY `dock` (`dock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_pages_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`page_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`content` MEDIUMTEXT NOT NULL DEFAULT '',
	`head_title` VARCHAR(128) NOT NULL DEFAULT '',
	`meta_description` VARCHAR(512) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `page_info` (`page_id`, `language_code`),
	KEY `page_id` (`page_id`),
	KEY `language_code` (`language_code`),
	CONSTRAINT `page_info_to_page` FOREIGN KEY (`page_id`) REFERENCES `lc_pages` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `page_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_products` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`type` ENUM('virtual','physical','digital','variable','bundle') NOT NULL DEFAULT 'virtual',
	`brand_id` INT(10) UNSIGNED NULL,
	`supplier_id` INT(10) UNSIGNED NULL,
	`delivery_status_id` INT(10) UNSIGNED NULL,
	`sold_out_status_id` INT(10) UNSIGNED NULL,
	`default_category_id` INT(10) UNSIGNED NULL,
	`code` VARCHAR(32) NOT NULL DEFAULT '',
	`sku` VARCHAR(32) NOT NULL DEFAULT '',
	`mpn` VARCHAR(32) NOT NULL DEFAULT '',
	`gtin` VARCHAR(32) NOT NULL DEFAULT '',
	`taric` VARCHAR(16) NOT NULL DEFAULT '',
	`keywords` VARCHAR(255) NOT NULL DEFAULT '',
	`synonyms` VARCHAR(255) NOT NULL DEFAULT '',
	`quantity` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`quantity_min` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '1.0000',
	`quantity_max` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`quantity_step` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`quantity_unit_id` INT(10) UNSIGNED NULL,
	`weight` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
	`weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
	`length` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
	`width` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
	`height` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
	`length_unit` VARCHAR(2) NOT NULL DEFAULT '',
	`backordered` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`recommended_price` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`tax_class_id` INT(10) UNSIGNED NULL,
	`image` VARCHAR(255) NOT NULL DEFAULT '',
	`file` VARCHAR(128) NOT NULL DEFAULT '',
	`filename` VARCHAR(128) NOT NULL DEFAULT '',
	`mime_type` VARCHAR(32) NOT NULL DEFAULT '',
	`autofill_technical_data` TINYINT(1) NOT NULL DEFAULT '0',
	`views` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`purchases` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`date_valid_from` TIMESTAMP NULL DEFAULT NULL,
	`date_valid_to` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `status` (`status`),
	KEY `default_category_id` (`default_category_id`),
	KEY `brand_id` (`brand_id`),
	KEY `keywords` (`keywords`),
	KEY `synonyms` (`keywords`),
	KEY `code` (`code`),
	KEY `sku` (`sku`),
	KEY `mpn` (`mpn`),
	KEY `gtin` (`gtin`),
	KEY `taric` (`taric`),
	KEY `date_valid_from` (`date_valid_from`),
	KEY `date_valid_to` (`date_valid_to`),
	KEY `purchases` (`purchases`),
	KEY `views` (`views`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_products_attributes` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(10) UNSIGNED NOT NULL,
	`group_id` INT(10) UNSIGNED NOT NULL,
	`value_id` INT(10) UNSIGNED NULL,
	`custom_value` VARCHAR(255) NOT NULL DEFAULT '',
	`priority` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `id` (`id`, `product_id`, `group_id`, `value_id`),
	KEY `product_id` (`product_id`),
	KEY `group_id` (`group_id`),
	KEY `value_id` (`value_id`),
	CONSTRAINT `product_attribute_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `product_attribute_to_attribute_group` FOREIGN KEY (`group_id`) REFERENCES `lc_attribute_groups` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `product_attribute_to_attribute_value` FOREIGN KEY (`value_id`) REFERENCES `lc_attribute_values` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_products_images` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(10) UNSIGNED NOT NULL,
	`filename` VARCHAR(255) NOT NULL DEFAULT '',
	`checksum` CHAR(32) NULL,
	`priority` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `product_id` (`product_id`),
	CONSTRAINT `product_image_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_products_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '',
	`description` TEXT NOT NULL DEFAULT '',
	`technical_data` TEXT NOT NULL DEFAULT '',
	`head_title` VARCHAR(128) NOT NULL DEFAULT '',
	`meta_description` VARCHAR(512) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `product_info` (`product_id`, `language_code`),
	KEY `product_id` (`product_id`),
	KEY `language_code` (`language_code`),
	FULLTEXT KEY `name` (`name`),
	FULLTEXT KEY `short_description` (`short_description`),
	FULLTEXT KEY `description` (`description`),
	CONSTRAINT `product_info_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `product_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_products_prices` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(10) UNSIGNED NOT NULL,
	`USD` FLOAT(11) NOT NULL DEFAULT '0.0000',
	`EUR` FLOAT(11) NOT NULL DEFAULT '0.0000',
	PRIMARY KEY (`id`),
	KEY `product_id` (`product_id`),
	CONSTRAINT `product_price_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_products_stock_options` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(10) UNSIGNED NOT NULL,
	`stock_item_id` INT(10) UNSIGNED NOT NULL,
	`price_operator` ENUM('+','%','*','=') NOT NULL DEFAULT '+',
	`USD` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`EUR` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`priority` INT(11) NOT NULL DEFAULT '0.0000',
	PRIMARY KEY (`id`),
	UNIQUE KEY `stock_option` (`product_id`, `stock_item_id`),
	KEY `product_id` (`product_id`),
	CONSTRAINT `product_stock_option_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
	CONSTRAINT `product_stock_option_to_stock_item` FOREIGN KEY (`stock_item_id`) REFERENCES `lc_stock_items` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_products_to_categories` (
	`product_id` INT(10) UNSIGNED NOT NULL,
	`category_id` INT(10) UNSIGNED NULL,
	PRIMARY KEY(`product_id`, `category_id`),
	CONSTRAINT `product_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `product_to_category` FOREIGN KEY (`category_id`) REFERENCES `lc_categories` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_quantity_units` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`decimals` TINYINT(1) UNSIGNED NOT NULL DEFAULT '2',
	`separate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_quantity_units_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`quantity_unit_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`name` VARCHAR(32) NOT NULL DEFAULT '',
	`description` VARCHAR(512),
	PRIMARY KEY (`id`),
	UNIQUE KEY `quantity_unit_info` (`quantity_unit_id`, `language_code`),
	KEY `quantity_unit_id` (`quantity_unit_id`),
	KEY `language_code` (`language_code`),
	CONSTRAINT `quantity_unit_info_to_quantity_unit` FOREIGN KEY (`quantity_unit_id`) REFERENCES `lc_quantity_units` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `quantity_unit_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_settings` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`group_key` VARCHAR(32) NULL,
	`type` ENUM('global','local') NOT NULL DEFAULT 'local',
	`key` VARCHAR(32) NOT NULL DEFAULT '',
	`value` VARCHAR(255) NOT NULL DEFAULT '',
	`title` VARCHAR(128) NOT NULL DEFAULT '',
	`description` VARCHAR(512) NOT NULL DEFAULT '',
	`required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`function` VARCHAR(128) NOT NULL DEFAULT '',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `key` (`key`),
	KEY `type` (`type`),
	KEY `group_key` (`group_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_settings_groups` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`key` VARCHAR(32) NOT NULL,
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`description` VARCHAR(255) NOT NULL DEFAULT '',
	`priority` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_sold_out_statuses` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`orderable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `hidden` (`hidden`),
	KEY `orderable` (`orderable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_sold_out_statuses_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`sold_out_status_id` INT(10) UNSIGNED NOT NULL,
	`language_code` CHAR(2) NOT NULL,
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`description` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `sold_out_status_info` (`sold_out_status_id`, `language_code`),
	KEY `sold_out_status_id` (`sold_out_status_id`),
	KEY `language_code` (`language_code`),
	CONSTRAINT `sold_out_status_info_to_sold_out_status` FOREIGN KEY (`sold_out_status_id`) REFERENCES `lc_sold_out_statuses` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `sold_out_status_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_stock_items` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`brand_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`supplier_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
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
	`backordered` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`purchase_price` FLOAT(11,4) NOT NULL DEFAULT '0.0000',
	`purchase_price_currency_code` VARCHAR(3) NOT NULL DEFAULT '',
	`priority` INT(11) NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `mpn` (`mpn`) USING BTREE,
	INDEX `gtin` (`gtin`) USING BTREE,
	INDEX `sku` (`sku`) USING BTREE,
	INDEX `product_id` (`product_id`) USING BTREE,
	INDEX `brand_id` (`brand_id`) USING BTREE,
	INDEX `supplier_id` (`supplier_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_stock_items_info` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`stock_item_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`language_code` VARCHAR(2) NOT NULL DEFAULT '',
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `stock_option_id` (`stock_item_id`) USING BTREE,
	INDEX `language_code` (`language_code`) USING BTREE,
	FULLTEXT INDEX `name` (`name`),
	CONSTRAINT `stock_item_info_to_language` FOREIGN KEY (`language_code`) REFERENCES `lc_languages` (`code`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `stock_item_info_to_stock_item` FOREIGN KEY (`stock_item_id`) REFERENCES `lc_stock_items` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_stock_items_references` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`stock_item_id` INT(10) UNSIGNED NOT NULL,
	`supplier_id` INT(10) UNSIGNED NOT NULL DEFAULT '',
	`code` VARCHAR(32) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `stock_item_id` (`stock_item_id`) USING BTREE,
	CONSTRAINT `stock_item` FOREIGN KEY (`stock_item_id`) REFERENCES `lc_stock_items` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `supplier` FOREIGN KEY (`supplier_id`) REFERENCES `lc_suppliers` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_stock_transactions` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	`description` MEDIUMTEXT NOT NULL DEFAULT '',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_stock_transactions_contents` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`transaction_id` INT(10) UNSIGNED NOT NULL,
	`product_id` INT(10) UNSIGNED NOT NULL,
	`sku` VARCHAR(32) NOT NULL,
	`quantity_adjustment` FLOAT(11) NOT NULL DEFAULT '0.0000',
	PRIMARY KEY (`id`),
	KEY `transaction_id` (`transaction_id`),
	KEY `product_id` (`product_id`),
	KEY `stock_option_id` (`stock_option_id`),
	CONSTRAINT `stock_transaction_content_to_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `lc_stock_transactions` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `stock_transaction_content_to_product` FOREIGN KEY (`product_id`) REFERENCES `lc_products` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT `stock_transaction_content_to_stock_item` FOREIGN KEY (`stock_option_id`) REFERENCES `lc_products_stock_options` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_suppliers` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`code` VARCHAR(64) NOT NULL DEFAULT '',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`description` TEXT NOT NULL DEFAULT '',
	`email` VARCHAR(128) NOT NULL DEFAULT '',
	`phone` VARCHAR(24) NOT NULL DEFAULT '',
	`link` VARCHAR(255) NOT NULL DEFAULT '',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_tax_classes` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`code` VARCHAR(32) NOT NULL DEFAULT '',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`description` VARCHAR(64) NOT NULL DEFAULT '',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_tax_rates` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`tax_class_id` INT(10) UNSIGNED NOT NULL,
	`geo_zone_id` INT(10) UNSIGNED NOT NULL,
	`code` VARCHAR(32) NOT NULL DEFAULT '',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`description` VARCHAR(128) NOT NULL DEFAULT '',
	`rate` FLOAT(6,4) UNSIGNED NOT NULL DEFAULT '0.0000',
	`address_type` ENUM('shipping','payment') NOT NULL DEFAULT 'shipping',
	`rule_companies_with_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`rule_companies_without_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`rule_individuals_with_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`rule_individuals_without_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `tax_class_id` (`tax_class_id`),
	KEY `geo_zone_id` (`geo_zone_id`),
	CONSTRAINT `tax_rate_to_tax_class` FOREIGN KEY (`tax_class_id`) REFERENCES `lc_tax_classes` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `tax_rate_to_geo_zone` FOREIGN KEY (`geo_zone_id`) REFERENCES `lc_geo_zones` (`id`) ON UPDATE NO ACTION ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_translations` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`code` VARCHAR(128) NOT NULL DEFAULT '',
	`text_en` TEXT NOT NULL DEFAULT '',
	`html` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`frontend` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`backend` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`date_accessed` TIMESTAMP NULL DEFAULT NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `code` (`code`),
	KEY `frontend` (`frontend`),
	KEY `backend` (`backend`),
	KEY `date_created` (`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_zones` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`country_code` CHAR(2) NOT NULL,
	`code` VARCHAR(8) NOT NULL,
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `country_code` (`country_code`),
	KEY `code` (`code`)
	CONSTRAINT `zone_to_country` FOREIGN KEY (`country_code`) REFERENCES `lc_countries` (`iso_code_2`) ON UPDATE NO ACTION ON DELETE CASCADE,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
-- -----
CREATE TABLE `lc_zones_to_geo_zones` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`geo_zone_id` INT(10) UNSIGNED NOT NULL,
	`country_code` CHAR(2) NOT NULL,
	`zone_code` VARCHAR(8) NULL,
	`city` VARCHAR(32) NULL,
	`date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `region` (`geo_zone_id`, `country_code`, `zone_code`, `city`),
	KEY `geo_zone_id` (`geo_zone_id`),
	KEY `country_code` (`country_code`),
	KEY `zone_code` (`zone_code`),
	KEY `city` (`city`),
	CONSTRAINT `zone_entry_to_geo_zone` FOREIGN KEY (`geo_zone_id`) REFERENCES `lc_geo_zones` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `zone_entry_to_country` FOREIGN KEY (`country_code`) REFERENCES `lc_countries` (`iso_code_2`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `zone_entry_to_zone` FOREIGN KEY (`zone_code`) REFERENCES `lc_zones` (`code`) ON UPDATE CASCADE ON DELETE CASCADE;
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
