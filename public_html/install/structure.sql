CREATE TABLE IF NOT EXISTS `lc_attribute_groups` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `sort` ENUM('alphabetical','priority') NOT NULL DEFAULT 'alphabetical',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_attribute_groups_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_group` (`group_id`,`language_code`),
  KEY `group_id` (`group_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_attribute_values` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_attribute_values_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `value_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_value` (`value_id`,`language_code`),
  KEY `value_id` (`value_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_cart_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `cart_uid` VARCHAR(13) NOT NULL DEFAULT '',
  `key` VARCHAR(32) NOT NULL DEFAULT '',
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `options` VARCHAR(2048) NOT NULL DEFAULT '',
  `quantity` FLOAT(11, 4) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `cart_uid` (`cart_uid`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `google_taxonomy_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `code` VARCHAR(64) NOT NULL DEFAULT '',
  `list_style` VARCHAR(32) NOT NULL DEFAULT '',
  `keywords` VARCHAR(256) NOT NULL DEFAULT '',
  `image` VARCHAR(256) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories_filters` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `attribute_group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `select_multiple` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `attribute_filter` (`category_id`, `attribute_group_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `short_description` VARCHAR(256) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL DEFAULT '',
  `head_title` VARCHAR(128) NOT NULL DEFAULT '',
  `h1_title` VARCHAR(128) NOT NULL DEFAULT '',
  `meta_description` VARCHAR(512) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category` (`category_id`, `language_code`),
  KEY `category_id` (`category_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_countries` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `domestic_name` VARCHAR(64) NOT NULL DEFAULT '',
  `iso_code_1` VARCHAR(3) NOT NULL DEFAULT '',
  `iso_code_2` VARCHAR(2) NOT NULL DEFAULT '',
  `iso_code_3` VARCHAR(3) NOT NULL DEFAULT '',
  `tax_id_format` VARCHAR(64) NOT NULL DEFAULT '',
  `address_format` VARCHAR(128) NOT NULL DEFAULT '',
  `postcode_format` VARCHAR(512) NOT NULL DEFAULT '',
  `postcode_required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `currency_code` VARCHAR(3) NOT NULL DEFAULT '',
  `phone_code` VARCHAR(3) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_code_1` (`iso_code_1`),
  UNIQUE KEY `iso_code_2` (`iso_code_2`),
  UNIQUE KEY `iso_code_3` (`iso_code_3`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_currencies` (
  `id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `code` VARCHAR(3) NOT NULL DEFAULT '',
  `number` VARCHAR(3) NOT NULL DEFAULT '',
  `name` VARCHAR(32) NOT NULL DEFAULT '',
  `value` FLOAT(11,6) UNSIGNED NOT NULL DEFAULT '0',
  `decimals` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `prefix` VARCHAR(8) NOT NULL DEFAULT '',
  `suffix` VARCHAR(8) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_customers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `email` VARCHAR(128) NOT NULL DEFAULT '',
  `password_hash` VARCHAR(256) NOT NULL DEFAULT '',
  `tax_id` VARCHAR(32) NOT NULL DEFAULT '',
  `company` VARCHAR(64) NOT NULL DEFAULT '',
  `firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `address1` VARCHAR(64) NOT NULL DEFAULT '',
  `address2` VARCHAR(64) NOT NULL DEFAULT '',
  `postcode` VARCHAR(16) NOT NULL DEFAULT '',
  `city` VARCHAR(32) NOT NULL DEFAULT '',
  `country_code` VARCHAR(4) NOT NULL DEFAULT '',
  `zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `phone` VARCHAR(24) NOT NULL DEFAULT '',
  `different_shipping_address` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `shipping_company` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address1` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address2` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_city` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_postcode` VARCHAR(16) NOT NULL DEFAULT '',
  `shipping_country_code` VARCHAR(4) NOT NULL DEFAULT '',
  `shipping_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `shipping_phone` VARCHAR(24) NOT NULL DEFAULT '',
  `notes` TEXT NOT NULL DEFAULT '',
  `password_reset_token` VARCHAR(128) NOT NULL DEFAULT '',
  `login_attempts` INT NOT NULL DEFAULT '0',
  `total_logins` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `last_ip` VARCHAR(39) NOT NULL DEFAULT '',
  `last_host` VARCHAR(128) NOT NULL DEFAULT '',
  `last_agent` VARCHAR(256) NOT NULL DEFAULT '',
  `date_login` TIMESTAMP NULL DEFAULT NULL,
  `date_blocked_until` TIMESTAMP NULL DEFAULT NULL,
  `date_expire_sessions` TIMESTAMP NULL DEFAULT NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_status` (`delivery_status_id`, `language_code`),
  KEY `delivery_status_id` (`delivery_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_emails` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` ENUM('draft','scheduled','sent','error') NOT NULL DEFAULT 'draft',
  `code` VARCHAR(256) NOT NULL DEFAULT '',
  `charset` VARCHAR(16) NOT NULL DEFAULT '',
  `sender` VARCHAR(256) NOT NULL DEFAULT '',
  `recipients` TEXT NOT NULL DEFAULT '',
  `ccs` TEXT NOT NULL DEFAULT '',
  `bccs` TEXT NOT NULL DEFAULT '',
  `subject` VARCHAR(256) NOT NULL DEFAULT '',
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
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_geo_zones` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(256) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_languages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `code` VARCHAR(2) NOT NULL DEFAULT '',
  `code2` VARCHAR(3) NOT NULL DEFAULT '',
  `name` VARCHAR(32) NOT NULL DEFAULT '',
  `direction` ENUM('ltr','rtl') NOT NULL DEFAULT 'ltr',
  `locale` VARCHAR(64) NOT NULL DEFAULT '',
  `charset` VARCHAR(16) NOT NULL DEFAULT '',
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
  `currency_code` VARCHAR(3) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY `id` (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_manufacturers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `featured` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `keywords` VARCHAR(256) NOT NULL DEFAULT '',
  `image` VARCHAR(256) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_manufacturers_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `manufacturer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `short_description` VARCHAR(256) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL DEFAULT '',
  `h1_title` VARCHAR(128) NOT NULL DEFAULT '',
  `head_title` VARCHAR(128) NOT NULL DEFAULT '',
  `meta_description` VARCHAR(512) NOT NULL DEFAULT '',
  `link` VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_info` (`manufacturer_id`, `language_code`),
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_modules` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_id` VARCHAR(64) NOT NULL DEFAULT '',
  `type` VARCHAR(16) NOT NULL DEFAULT '',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_newsletter_recipients` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(128) NOT NULL DEFAULT '',
  `client_ip` VARCHAR(64) NOT NULL DEFAULT '',
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` VARCHAR(13) NOT NULL DEFAULT '',
  `starred` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `unread` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `order_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `customer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `customer_company` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_email` VARCHAR(128) NOT NULL DEFAULT '',
  `customer_phone` VARCHAR(24) NOT NULL DEFAULT '',
  `customer_tax_id` VARCHAR(32) NOT NULL DEFAULT '',
  `customer_address1` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_address2` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_city` VARCHAR(32) NOT NULL DEFAULT '',
  `customer_postcode` VARCHAR(16) NOT NULL DEFAULT '',
  `customer_country_code` VARCHAR(2) NOT NULL DEFAULT '',
  `customer_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `shipping_company` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address1` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address2` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_city` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_postcode` VARCHAR(16) NOT NULL DEFAULT '',
  `shipping_country_code` VARCHAR(2) NOT NULL DEFAULT '',
  `shipping_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `shipping_phone` VARCHAR(24) NOT NULL DEFAULT '',
  `shipping_option_id` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_option_name` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_tracking_id` VARCHAR(128) NOT NULL DEFAULT '',
  `shipping_tracking_url` VARCHAR(256) NOT NULL DEFAULT '',
  `payment_option_id` VARCHAR(32) NOT NULL DEFAULT '',
  `payment_option_name` VARCHAR(64) NOT NULL DEFAULT '',
  `payment_transaction_id` VARCHAR(128) NOT NULL DEFAULT '',
  `reference` VARCHAR(128) NOT NULL DEFAULT '',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `weight_total` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `weight_class` VARCHAR(2) NOT NULL DEFAULT '',
  `currency_code` VARCHAR(3) NOT NULL DEFAULT '',
  `currency_value` FLOAT(11,6) UNSIGNED NOT NULL DEFAULT '0',
  `display_prices_including_tax` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `payment_due` FLOAT(11,4) NOT NULL DEFAULT '0',
  `tax_total` FLOAT(11,4) NOT NULL DEFAULT '0',
  `client_ip` VARCHAR(39) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(256) NOT NULL DEFAULT '',
  `domain` VARCHAR(64) NOT NULL DEFAULT '',
  `public_key` VARCHAR(32) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_status_id` (`order_status_id`),
  KEY `starred` (`starred`),
  KEY `unread` (`unread`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_comments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `author` enum('system','staff','customer') NOT NULL DEFAULT 'system',
  `text` VARCHAR(512) NOT NULL DEFAULT '',
  `hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `option_stock_combination` VARCHAR(32) NOT NULL DEFAULT '',
  `options` VARCHAR(4096) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `sku` VARCHAR(32) NOT NULL DEFAULT '',
  `gtin` VARCHAR(32) NOT NULL DEFAULT '',
  `taric` VARCHAR(32) NOT NULL DEFAULT '',
  `quantity` FLOAT(11,4) NOT NULL DEFAULT '0',
  `price` FLOAT(11,4) NOT NULL DEFAULT '0',
  `tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `weight` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `weight_class` VARCHAR(2) NOT NULL DEFAULT '',
  `dim_x` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `dim_y` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `dim_z` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `dim_class` VARCHAR(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `state` ENUM('','created','on_hold','ready','delayed','processing','dispatched','in_transit','delivered','returning','returned','cancelled','fraud') NOT NULL DEFAULT ''
  `icon` VARCHAR(24) NOT NULL DEFAULT '',
  `color` VARCHAR(7) NOT NULL DEFAULT '',
  `keywords` VARCHAR(256) NOT NULL DEFAULT '',
  `is_sale` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `is_archived` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `is_trackable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `stock_action` ENUM('none','reserve','commit') NOT NULL DEFAULT 'none',
  `notify` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_sale` (`is_sale`),
  KEY `is_archived` (`is_archived`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(256) NOT NULL DEFAULT '',
  `email_subject` VARCHAR(128) NOT NULL DEFAULT '',
  `email_message` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_status_info` (`order_status_id`, `language_code`),
  KEY `order_status_id` (`order_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_totals` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `module_id` VARCHAR(32) NOT NULL DEFAULT '',
  `title` VARCHAR(128) NOT NULL DEFAULT '',
  `value` FLOAT(11,4) NOT NULL DEFAULT '0',
  `tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `calculate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_pages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `parent_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `dock` VARCHAR(64) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `parent_id` (`parent_id`),
  KEY `dock` (`dock`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_pages_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `title` VARCHAR(256) NOT NULL DEFAULT '',
  `content` MEDIUMTEXT NOT NULL DEFAULT '',
  `head_title` VARCHAR(128) NOT NULL DEFAULT '',
  `meta_description` VARCHAR(512) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_info` (`page_id`, `language_code`),
  KEY `page_id` (`page_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `manufacturer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `supplier_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `delivery_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `sold_out_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `default_category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `keywords` VARCHAR(256) NOT NULL DEFAULT '',
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `sku` VARCHAR(32) NOT NULL DEFAULT '',
  `mpn` VARCHAR(32) NOT NULL DEFAULT '',
  `upc` VARCHAR(24) NOT NULL DEFAULT '' COMMENT 'Deprecated, use GTIN',
  `gtin` VARCHAR(32) NOT NULL DEFAULT '',
  `taric` VARCHAR(16) NOT NULL DEFAULT '',
  `quantity` FLOAT(11,4) NOT NULL DEFAULT '0',
  `quantity_min` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
  `quantity_max` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
  `quantity_step` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0.0000',
  `quantity_unit_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `weight` FLOAT(10,4) UNSIGNED NOT NULL DEFAULT '0',
  `weight_class` VARCHAR(2) NOT NULL DEFAULT '',
  `dim_x` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `dim_y` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `dim_z` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `dim_class` VARCHAR(2) NOT NULL DEFAULT '',
  `purchase_price` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `purchase_price_currency_code` VARCHAR(3) NOT NULL DEFAULT '',
  `recommended_price` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `tax_class_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `image` VARCHAR(256) NOT NULL DEFAULT '',
  `views` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `purchases` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `date_valid_from` TIMESTAMP NULL DEFAULT NULL,
  `date_valid_to` TIMESTAMP NULL DEFAULT NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `default_category_id` (`default_category_id`),
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `keywords` (`keywords`),
  KEY `code` (`code`),
  KEY `sku` (`sku`),
  KEY `mpn` (`mpn`),
  KEY `gtin` (`gtin`),
  KEY `taric` (`taric`),
  KEY `date_valid_from` (`date_valid_from`),
  KEY `date_valid_to` (`date_valid_to`),
  KEY `purchases` (`purchases`),
  KEY `views` (`views`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_attributes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `value_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `custom_value` VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id` (`id`, `product_id`, `group_id`, `value_id`),
  KEY `product_id` (`product_id`),
  KEY `group_id` (`group_id`),
  KEY `value_id` (`value_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_campaigns` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `start_date` TIMESTAMP NULL DEFAULT NULL,
  `end_date` TIMESTAMP NULL DEFAULT NULL,
  `USD` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `EUR` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_images` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `filename` VARCHAR(256) NOT NULL DEFAULT '',
  `checksum` VARCHAR(32) NULL,
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `short_description` VARCHAR(256) NOT NULL DEFAULT '',
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
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_options` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `function` VARCHAR(32) NOT NULL DEFAULT '',
  `required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `sort` VARCHAR(16) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `product_option` (`product_id`, `group_id`),
  INDEX `product_id` (`product_id`),
  INDEX `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_options_values` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `value_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `custom_value` VARCHAR(64) NOT NULL DEFAULT '',
  `price_operator` VARCHAR(1) NOT NULL DEFAULT '',
  `USD` FLOAT(11,4) NOT NULL DEFAULT '0',
  `EUR` FLOAT(11,4) NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `product_option_value` (`product_id`, `group_id`, `value_id`, `custom_value`),
  INDEX `product_id` (`product_id`),
  INDEX `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_options_stock` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `combination` VARCHAR(64) NOT NULL DEFAULT '',
  `sku` VARCHAR(64) NOT NULL DEFAULT '',
  `weight` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `weight_class` VARCHAR(2) NOT NULL DEFAULT '',
  `dim_x` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `dim_y` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `dim_z` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `dim_class` VARCHAR(2) NOT NULL DEFAULT '',
  `quantity` FLOAT(11,4) NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_option_stock` (`product_id`, `combination`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_prices` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `USD` FLOAT(11,4) NOT NULL DEFAULT '0',
  `EUR` FLOAT(11,4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_to_categories` (
   `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
   `category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
   PRIMARY KEY(`product_id`, `category_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `decimals` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `separate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quantity_unit_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(32) NOT NULL DEFAULT '',
  `description` VARCHAR(512),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quantity_unit_info` (`quantity_unit_id`, `language_code`),
  KEY `quantity_unit_id` (`quantity_unit_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_group_key` VARCHAR(64) NOT NULL DEFAULT '',
  `type` enum('global','local') NOT NULL DEFAULT 'local',
  `key` VARCHAR(64) NOT NULL DEFAULT '',
  `value` VARCHAR(8192) NOT NULL DEFAULT '',
  `title` VARCHAR(128) NOT NULL DEFAULT '',
  `description` VARCHAR(512) NOT NULL DEFAULT '',
  `function` VARCHAR(128) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `setting_group_key` (`setting_group_key`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings_groups` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(64) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(256) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_slides` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `languages` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `image` VARCHAR(256) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_valid_from` TIMESTAMP NULL DEFAULT NULL,
  `date_valid_to` TIMESTAMP NULL DEFAULT NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_slides_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slide_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `caption` TEXT NOT NULL DEFAULT '',
  `link` VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slide_info` (`slide_id`,`language_code`),
  KEY `slide_id` (`slide_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_sold_out_statuses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `orderable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `hidden` (`hidden`),
  KEY `orderable` (`orderable`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_sold_out_statuses_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sold_out_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` VARCHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sold_out_status_info` (`sold_out_status_id`, `language_code`),
  KEY `sold_out_status_id` (`sold_out_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_suppliers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL DEFAULT '',
  `email` VARCHAR(128) NOT NULL DEFAULT '',
  `phone` VARCHAR(24) NOT NULL DEFAULT '',
  `link` VARCHAR(256) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_classes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(64) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_rates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tax_class_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `geo_zone_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(128) NOT NULL DEFAULT '',
  `rate` FLOAT(10,4) NOT NULL DEFAULT '0',
  `type` ENUM('fixed','percent') NOT NULL DEFAULT 'percent',
  `address_type` ENUM('shipping','payment') NOT NULL DEFAULT 'shipping',
  `rule_companies_with_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `rule_companies_without_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `rule_individuals_with_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `rule_individuals_without_tax_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tax_class_id` (`tax_class_id`),
  KEY `geo_zone_id` (`geo_zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_translations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(250) NOT NULL DEFAULT '',
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
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `username` VARCHAR(32) NOT NULL DEFAULT '',
  `email` VARCHAR(128) NOT NULL DEFAULT '',
  `password_hash` VARCHAR(256) NOT NULL DEFAULT '',
  `apps` VARCHAR(4096) NOT NULL DEFAULT '',
  `widgets` VARCHAR(512) NOT NULL DEFAULT '',
  `last_ip` VARCHAR(39) NOT NULL DEFAULT '',
  `last_host` VARCHAR(128) NOT NULL DEFAULT '',
  `login_attempts` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `total_logins` INT(11) UNSIGNED NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_code` VARCHAR(4) NOT NULL DEFAULT '',
  `code` VARCHAR(8) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones_to_geo_zones` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `geo_zone_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `country_code` VARCHAR(2) NOT NULL DEFAULT '',
  `zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `city` VARCHAR(32) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `region` (`geo_zone_id`, `country_code`, `zone_code`, `city`),
  KEY `geo_zone_id` (`geo_zone_id`),
  KEY `country_code` (`country_code`),
  KEY `zone_code` (`zone_code`),
  KEY `city` (`city`)
) ENGINE=MyISAM DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};