CREATE TABLE IF NOT EXISTS `lc_attribute_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_attribute_groups_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `name` VARCHAR(64) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_group` (`group_id`,`language_code`),
  KEY `group_id` (`group_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_attribute_values` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) NULL,
  `priority` INT(11) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_attribute_values_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `value_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `name` VARCHAR(64) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_value` (`value_id`,`language_code`),
  KEY `value_id` (`value_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_brands` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NULL,
  `featured` TINYINT(1) NULL,
  `code` VARCHAR(32) NULL,
  `name` VARCHAR(64) NULL,
  `keywords` VARCHAR(256) NULL,
  `image` VARCHAR(256) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_brands_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `brand_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `short_description` VARCHAR(256) NULL,
  `description` TEXT NULL,
  `h1_title` VARCHAR(128) NULL,
  `head_title` VARCHAR(128) NULL,
  `meta_description` VARCHAR(512) NULL,
  `link` VARCHAR(256) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_info` (`brand_id`, `language_code`),
  KEY `brand_id` (`brand_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_cart_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NULL,
  `cart_uid` VARCHAR(13) NULL,
  `key` VARCHAR(32) NULL,
  `product_id` INT(11) NULL,
  `options` VARCHAR(2048) NULL,
  `quantity` DECIMAL(11, 4) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `cart_uid` (`cart_uid`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) NULL,
  `google_taxonomy_id` INT(11) NULL,
  `status` TINYINT(1) NULL,
  `code` VARCHAR(64) NULL,
  `list_style` VARCHAR(32) NULL,
  `keywords` VARCHAR(256) NULL,
  `image` VARCHAR(256) NULL,
  `priority` TINYINT(2) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories_filters` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NULL,
  `attribute_group_id` INT(11) NULL,
  `select_multiple` TINYINT(1) NULL,
  `priority` INT(11) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `attribute_filter` (`category_id`, `attribute_group_id`),
  INDEX `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `name` VARCHAR(128) NULL,
  `short_description` VARCHAR(256) NULL,
  `description` TEXT NULL,
  `head_title` VARCHAR(128) NULL,
  `h1_title` VARCHAR(128) NULL,
  `meta_description` VARCHAR(512) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category` (`category_id`, `language_code`),
  KEY `category_id` (`category_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_countries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `name` VARCHAR(64) NULL,
  `domestic_name` VARCHAR(64) CHARACTER SET utf8 NULL,
  `iso_code_1` VARCHAR(3) NULL,
  `iso_code_2` VARCHAR(2) NULL,
  `iso_code_3` VARCHAR(3) NULL,
  `tax_id_format` VARCHAR(64) NULL,
  `address_format` VARCHAR(128) NULL,
  `postcode_format` VARCHAR(512) NULL,
  `postcode_required` TINYINT(1) NULL,
  `language_code` VARCHAR(2) CHARACTER SET utf8 NULL,
  `currency_code` VARCHAR(3) CHARACTER SET utf8 NULL,
  `phone_code` VARCHAR(3) CHARACTER SET utf8 NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_code_1` (`iso_code_1`),
  UNIQUE KEY `iso_code_2` (`iso_code_2`),
  UNIQUE KEY `iso_code_3` (`iso_code_3`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_currencies` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NULL,
  `code` VARCHAR(3) NULL,
  `number` VARCHAR(3) NULL,
  `name` VARCHAR(32) NULL,
  `value` DECIMAL(11,6) NULL,
  `decimals` TINYINT(1) NULL,
  `prefix` VARCHAR(8) NULL,
  `suffix` VARCHAR(8) NULL,
  `priority` TINYINT(2) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `email` VARCHAR(128) NULL,
  `password_hash` VARCHAR(256) NULL,
  `tax_id` VARCHAR(32) NULL,
  `company` VARCHAR(64) NULL,
  `firstname` VARCHAR(64) NULL,
  `lastname` VARCHAR(64) NULL,
  `address1` VARCHAR(64) NULL,
  `address2` VARCHAR(64) NULL,
  `postcode` VARCHAR(16) NULL,
  `city` VARCHAR(32) NULL,
  `country_code` VARCHAR(4) NULL,
  `zone_code` VARCHAR(8) NULL,
  `phone` VARCHAR(24) NULL,
  `different_shipping_address` TINYINT(1) NULL,
  `shipping_company` VARCHAR(64) NULL,
  `shipping_firstname` VARCHAR(64) NULL,
  `shipping_lastname` VARCHAR(64) NULL,
  `shipping_address1` VARCHAR(64) NULL,
  `shipping_address2` VARCHAR(64) NULL,
  `shipping_city` VARCHAR(32) NULL,
  `shipping_postcode` VARCHAR(16) NULL,
  `shipping_country_code` VARCHAR(4) NULL,
  `shipping_zone_code` VARCHAR(8) NULL,
  `shipping_phone` VARCHAR(24) NULL,
  `newsletter` TINYINT(1) NOT NULL DEFAULT '1',
  `notes` TEXT NULL,
  `password_reset_token` VARCHAR(128) NULL,
  `num_logins` INT(11) NULL,
  `last_ip` VARCHAR(39) NULL,
  `last_host` VARCHAR(128) NULL,
  `last_agent` VARCHAR(256) NULL,
  `date_login` DATETIME NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `delivery_status_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `name` VARCHAR(64) NULL,
  `description` VARCHAR(256) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_status` (`delivery_status_id`, `language_code`),
  KEY `delivery_status_id` (`delivery_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_emails` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` ENUM('draft','scheduled','sent','error') NOT NULL DEFAULT 'draft',
  `code` VARCHAR(256) NULL,
  `charset` VARCHAR(16) NULL,
  `sender` VARCHAR(256) NULL,
  `recipients` TEXT NULL,
  `ccs` TEXT NULL,
  `bccs` TEXT NULL,
  `subject` VARCHAR(256) NULL,
  `multiparts` MEDIUMTEXT NULL,
  `date_scheduled` DATETIME NULL,
  `date_sent` DATETIME NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `date_scheduled` (`date_scheduled`),
  KEY `code` (`code`),
  KEY `date_created` (`date_created`),
  KEY `sender_email` (`sender`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_geo_zones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NULL,
  `name` VARCHAR(64) NULL,
  `description` VARCHAR(256) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_languages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NULL,
  `code` VARCHAR(2) NULL,
  `code2` VARCHAR(3) NULL,
  `name` VARCHAR(32) NULL,
  `locale` VARCHAR(32) NULL,
  `charset` VARCHAR(16) NULL,
  `raw_date` VARCHAR(32) NULL,
  `raw_time` VARCHAR(32) NULL,
  `raw_datetime` VARCHAR(32) NULL,
  `format_date` VARCHAR(32) NULL,
  `format_time` VARCHAR(32) NULL,
  `format_datetime` VARCHAR(32) NULL,
  `decimal_point` VARCHAR(1) NULL,
  `thousands_sep` VARCHAR(1) NULL,
  `currency_code` VARCHAR(3) NULL,
  `priority` TINYINT(2) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY `id` (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_modules` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `module_id` VARCHAR(64) NULL,
  `type` VARCHAR(16) NULL,
  `status` TINYINT(1) NULL,
  `priority` TINYINT(4) NULL,
  `settings` TEXT NULL,
  `last_log` TEXT NULL,
  `date_pushed` DATETIME NULL,
  `date_processed` DATETIME NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_id` (`module_id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_newsletter_recipients` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(128) NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uid` VARCHAR(13) NULL,
  `starred` TINYINT(1) NULL,
  `unread` TINYINT(1) NULL,
  `order_status_id` INT(11) NULL,
  `customer_id` INT(11) NULL,
  `customer_company` VARCHAR(64) NULL,
  `customer_firstname` VARCHAR(64) NULL,
  `customer_lastname` VARCHAR(64) NULL,
  `customer_email` VARCHAR(128) NULL,
  `customer_phone` VARCHAR(24) NULL,
  `customer_tax_id` VARCHAR(32) NULL,
  `customer_address1` VARCHAR(64) NULL,
  `customer_address2` VARCHAR(64) NULL,
  `customer_city` VARCHAR(32) NULL,
  `customer_postcode` VARCHAR(16) NULL,
  `customer_country_code` VARCHAR(2) NULL,
  `customer_zone_code` VARCHAR(8) NULL,
  `shipping_company` VARCHAR(64) NULL,
  `shipping_firstname` VARCHAR(64) NULL,
  `shipping_lastname` VARCHAR(64) NULL,
  `shipping_address1` VARCHAR(64) NULL,
  `shipping_address2` VARCHAR(64) NULL,
  `shipping_city` VARCHAR(32) NULL,
  `shipping_postcode` VARCHAR(16) NULL,
  `shipping_country_code` VARCHAR(2) NULL,
  `shipping_zone_code` VARCHAR(8) NULL,
  `shipping_phone` VARCHAR(24) NULL,
  `shipping_option_id` VARCHAR(32) NULL,
  `shipping_option_name` VARCHAR(64) NULL,
  `shipping_tracking_id` VARCHAR(128) NULL,
  `shipping_tracking_url` VARCHAR(256) NULL,
  `payment_option_id` VARCHAR(32) NULL,
  `payment_option_name` VARCHAR(64) NULL,
  `payment_transaction_id` VARCHAR(128) NULL,
  `reference` VARCHAR(128) NULL,
  `language_code` VARCHAR(2) NULL,
  `weight_total` DECIMAL(11,4) NULL,
  `weight_class` VARCHAR(2) NULL,
  `currency_code` VARCHAR(3) NULL,
  `currency_value` DECIMAL(11,6) NULL,
  `display_prices_including_tax` TINYINT(1) NULL,
  `payment_due` DECIMAL(11,4) NULL,
  `tax_total` DECIMAL(11,4) NULL,
  `client_ip` VARCHAR(39) NULL,
  `user_agent` VARCHAR(256) NULL,
  `domain` VARCHAR(64) NULL,
  `public_key` VARCHAR(32) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_status_id` (`order_status_id`),
  KEY `starred` (`starred`),
  KEY `unread` (`unread`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NULL,
  `author` enum('system','staff','customer') NULL,
  `text` VARCHAR(512) NULL,
  `hidden` TINYINT(1) NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NULL,
  `product_id` INT(11) NULL,
  `stock_option_id` INT(11) NULL,
  `options` VARCHAR(4096) NULL,
  `name` VARCHAR(128) NULL,
  `sku` VARCHAR(32) NULL,
  `gtin` VARCHAR(32) NULL,
  `taric` VARCHAR(32) NULL,
  `quantity` DECIMAL(11,4) NULL,
  `price` DECIMAL(11,4) NULL,
  `tax` DECIMAL(11,4) NULL,
  `weight` DECIMAL(11,4) NULL,
  `weight_class` VARCHAR(2) NULL,
  `dim_x` DECIMAL(11,4) NULL,
  `dim_y` DECIMAL(11,4) NULL,
  `dim_z` DECIMAL(11,4) NULL,
  `dim_class` VARCHAR(2) NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `stock_option_id` (`stock_option_id`),
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(24) NULL,
  `color` VARCHAR(7) NULL,
  `keywords` VARCHAR(256) NULL,
  `is_sale` TINYINT(1) NULL,
  `is_archived` TINYINT(1) NULL,
  `notify` TINYINT(1) NULL,
  `priority` TINYINT(2) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_sale` (`is_sale`),
  KEY `is_archived` (`is_archived`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_status_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `name` VARCHAR(64) NULL,
  `description` VARCHAR(256) NULL,
  `email_subject` VARCHAR(128) NULL,
  `email_message` VARCHAR(2048) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_status_info` (`order_status_id`, `language_code`),
  KEY `order_status_id` (`order_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_totals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NULL,
  `module_id` VARCHAR(32) NULL,
  `title` VARCHAR(128) NULL,
  `value` DECIMAL(11,4) NULL,
  `tax` DECIMAL(11,4) NULL,
  `calculate` TINYINT(1) NULL,
  `priority` TINYINT(2) NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_pages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NULL,
  `parent_id` INT(11) NULL,
  `dock` VARCHAR(64) NULL,
  `priority` TINYINT(2) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `parent_id` (`parent_id`),
  KEY `dock` (`dock`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_pages_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `page_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `title` VARCHAR(256) NULL,
  `content` mediumtext NULL,
  `head_title` VARCHAR(128) NULL,
  `meta_description` VARCHAR(512) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_info` (`page_id`, `language_code`),
  KEY `page_id` (`page_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NULL,
  `brand_id` INT(11) NULL,
  `supplier_id` INT(11) NULL,
  `delivery_status_id` INT(11) NULL,
  `sold_out_status_id` INT(11) NULL,
  `default_category_id` INT(11) NULL,
  `keywords` VARCHAR(256) NULL,
  `code` VARCHAR(32) NULL,
  `sku` VARCHAR(32) NULL,
  `mpn` VARCHAR(32) NULL,
  `upc` VARCHAR(24) NOT NULL COMMENT 'Deprecated, use GTIN',
  `gtin` VARCHAR(32) NULL,
  `taric` VARCHAR(16) NULL,
  `quantity` DECIMAL(11,4) NULL,
  `quantity_unit_id` INT(11) NULL,
  `weight` DECIMAL(10,4) NULL,
  `weight_class` VARCHAR(2) NULL,
  `dim_x` DECIMAL(10,4) NULL,
  `dim_y` DECIMAL(10,4) NULL,
  `dim_z` DECIMAL(10,4) NULL,
  `dim_class` VARCHAR(2) NULL,
  `purchase_price` DECIMAL(10,4) NULL,
  `purchase_price_currency_code` VARCHAR(3) NULL,
  `tax_class_id` INT(11) NULL,
  `image` VARCHAR(256) NULL,
  `views` INT(11) NULL,
  `purchases` INT(11) NULL,
  `date_valid_from` DATE NULL,
  `date_valid_to` DATE NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `default_category_id` (`default_category_id`),
  KEY `brand_id` (`brand_id`),
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
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_attributes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NULL,
  `group_id` INT(11) NULL,
  `value_id` INT(11) NULL,
  `custom_value` VARCHAR(256) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id` (`id`, `product_id`, `group_id`, `value_id`),
  INDEX `product_id` (`product_id`),
  INDEX `group_id` (`group_id`),
  INDEX `value_id` (`value_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_campaigns` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NULL,
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `USD` DECIMAL(11,4) NULL,
  `EUR` DECIMAL(11,4) NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NULL,
  `filename` VARCHAR(256) NULL,
  `checksum` CHAR(32) NULL,
  `priority` TINYINT(2) NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `name` VARCHAR(128) NULL,
  `short_description` VARCHAR(256) NULL,
  `description` TEXT NULL,
  `technical_data` TEXT NULL,
  `head_title` VARCHAR(128) NULL,
  `meta_description` VARCHAR(512) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_info` (`product_id`, `language_code`),
  KEY `product_id` (`product_id`),
  KEY `language_code` (`language_code`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `short_description` (`short_description`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_options` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NULL,
  `group_id` INT(11) NULL,
  `function` VARCHAR(32) NULL,
  `required` TINYINT(1) NULL,
  `sort` VARCHAR(16) NULL,
  `priority` TINYINT(2) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `product_option` (`product_id`, `group_id`),
  INDEX `product_id` (`product_id`),
  INDEX `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_options_values` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NULL,
  `group_id` INT(11) NULL,
  `value_id` INT(11) NULL,
  `custom_value` VARCHAR(64) NULL,
  `price_operator` VARCHAR(1) NULL,
  `USD` DECIMAL(11,4) NULL,
  `EUR` DECIMAL(11,4) NULL,
  `priority` TINYINT(2) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `product_option_value` (`product_id`, `group_id`, `value_id`),
  INDEX `product_id` (`product_id`),
  INDEX `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_stock` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NULL,
  `stock_item_id` INT(11) NULL,
  `sku` VARCHAR(64) NULL,
  `weight` DECIMAL(11,4) NULL,
  `weight_class` VARCHAR(2) NULL,
  `dim_x` DECIMAL(11,4) NULL,
  `dim_y` DECIMAL(11,4) NULL,
  `dim_z` DECIMAL(11,4) NULL,
  `dim_class` VARCHAR(2) NULL,
  `quantity` DECIMAL(11,4) NULL,
  `priority` TINYINT(2) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_option` (`product_id`, `combination`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_prices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NULL,
  `USD` DECIMAL(11,4) NULL,
  `EUR` DECIMAL(11,4) NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_to_categories` (
   `product_id` INT(11) NULL,
   `category_id` INT(11) NULL,
   PRIMARY KEY(`product_id`, `category_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `decimals` TINYINT(1) NULL,
  `separate` TINYINT(1) NULL,
  `priority` TINYINT(2) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quantity_unit_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `name` VARCHAR(32) NULL,
  `description` VARCHAR(512),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quantity_unit_info` (`quantity_unit_id`, `language_code`),
  KEY `quantity_unit_id` (`quantity_unit_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_group_key` VARCHAR(64) NULL,
  `type` enum('global','local') NOT NULL DEFAULT 'local',
  `key` VARCHAR(64) NULL,
  `title` VARCHAR(128) NULL,
  `description` VARCHAR(512) NULL,
  `value` VARCHAR(8192) NULL,
  `function` VARCHAR(128) NULL,
  `priority` TINYINT(2) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `setting_group_key` (`setting_group_key`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(64) NULL,
  `name` VARCHAR(64) NULL,
  `description` VARCHAR(256) NULL,
  `priority` TINYINT(2) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_slides` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NULL,
  `languages` VARCHAR(32) NULL,
  `name` VARCHAR(128) NULL,
  `image` VARCHAR(256) NULL,
  `priority` TINYINT(2) NULL,
  `date_valid_from` DATETIME NULL,
  `date_valid_to` DATETIME NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_slides_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `slide_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `caption` TEXT NULL,
  `link` VARCHAR(256) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slide_info` (`slide_id`,`language_code`),
  KEY `slide_id` (`slide_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_sold_out_statuses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `hidden` TINYINT(1) NULL,
  `orderable` TINYINT(1) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `hidden` (`hidden`),
  KEY `orderable` (`orderable`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_sold_out_statuses_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sold_out_status_id` INT(11) NULL,
  `language_code` VARCHAR(2) NULL,
  `name` VARCHAR(64) NULL,
  `description` VARCHAR(256) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sold_out_status_info` (`sold_out_status_id`, `language_code`),
  KEY `sold_out_status_id` (`sold_out_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_suppliers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) NULL,
  `name` VARCHAR(64) NULL,
  `description` TEXT NULL,
  `email` VARCHAR(128) NULL,
  `phone` VARCHAR(24) NULL,
  `link` VARCHAR(256) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_classes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NULL,
  `name` VARCHAR(64) NULL,
  `description` VARCHAR(64) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_rates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tax_class_id` INT(11) NULL,
  `geo_zone_id` INT(11) NULL,
  `code` VARCHAR(32) NULL,
  `name` VARCHAR(64) NULL,
  `description` VARCHAR(128) NULL,
  `rate` DECIMAL(10,4) NULL,
  `type` enum('fixed','percent') NOT NULL DEFAULT 'percent',
  `address_type` ENUM('shipping','payment') NOT NULL DEFAULT 'shipping',
  `rule_companies_with_tax_id` TINYINT(1) NULL,
  `rule_companies_without_tax_id` TINYINT(1) NULL,
  `rule_individuals_with_tax_id` TINYINT(1) NULL,
  `rule_individuals_without_tax_id` TINYINT(1) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tax_class_id` (`tax_class_id`),
  KEY `geo_zone_id` (`geo_zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_translations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(250) NULL,
  `text_en` TEXT NULL,
  `html` TINYINT(1) NULL,
  `frontend` TINYINT(1) NULL,
  `backend` TINYINT(1) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `frontend` (`frontend`),
  KEY `backend` (`backend`),
  KEY `date_created` (`date_created`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NULL,
  `username` VARCHAR(32) NULL,
  `email` VARCHAR(128) NULL,
  `password_hash` VARCHAR(256) NULL,
  `permissions` VARCHAR(4096) NULL,
  `last_ip` VARCHAR(39) NULL,
  `last_host` VARCHAR(128) NULL,
  `login_attempts` INT(11) NULL,
  `total_logins` INT(11) NULL,
  `date_valid_from` DATETIME NULL,
  `date_valid_to` DATETIME NULL,
  `date_active` DATETIME NULL,
  `date_login` DATETIME NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `country_code` VARCHAR(4) NULL,
  `code` VARCHAR(8) NULL,
  `name` VARCHAR(64) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones_to_geo_zones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `geo_zone_id` INT(11) NULL,
  `country_code` VARCHAR(2) NULL,
  `zone_code` VARCHAR(8) NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `region` (`geo_zone_id`, `country_code`, `zone_code`),
  KEY `geo_zone_id` (`geo_zone_id`),
  KEY `country_code` (`country_code`),
  KEY `zone_code` (`zone_code`)
) ENGINE=MyISAM DEFAULT CHARSET={DATABASE_CHARSET} COLLATE {DATABASE_COLLATION};
