CREATE TABLE `lc_addresses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,
  `company` VARCHAR(64) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `address1` VARCHAR(64) NOT NULL,
  `address2` VARCHAR(64) NOT NULL,
  `city` VARCHAR(32) NOT NULL,
  `postcode` VARCHAR(8) NOT NULL,
  `country_id` INT(11) NOT NULL,
  `zone_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_cart_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,
  `cart_uid` VARCHAR(13) NOT NULL,
  `key` VARCHAR(32) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `options` VARCHAR(2048) NOT NULL,
  `quantity` DECIMAL(11, 4) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `cart_uid` (`cart_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) NOT NULL,
  `google_taxonomy_id` INT(11) NOT NULL,
  `status` TINYINT(1) NOT NULL,
  `code` VARCHAR(64) NOT NULL,
  `list_style` VARCHAR(32) NOT NULL,
  `dock` VARCHAR(32) NOT NULL,
  `keywords` VARCHAR(256) NOT NULL,
  `image` VARCHAR(256) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`),
  KEY `dock` (`dock`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `short_description` VARCHAR(256) NOT NULL,
  `description` TEXT NOT NULL,
  `head_title` VARCHAR(128) NOT NULL,
  `h1_title` VARCHAR(128) NOT NULL,
  `meta_description` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `category` (`category_id`, `language_code`),
  KEY `category_id` (`category_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_countries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `name` VARCHAR(64) NOT NULL,
  `domestic_name` VARCHAR(64) CHARACTER SET utf8 NOT NULL,
  `iso_code_1` VARCHAR(3) NOT NULL,
  `iso_code_2` VARCHAR(2) NOT NULL,
  `iso_code_3` VARCHAR(3) NOT NULL,
  `tax_id_format` VARCHAR(64) NOT NULL,
  `address_format` VARCHAR(128) NOT NULL,
  `postcode_format` VARCHAR(512) NOT NULL,
  `postcode_required` TINYINT(1) NOT NULL,
  `language_code` VARCHAR(2) CHARACTER SET utf8 NOT NULL,
  `currency_code` VARCHAR(3) CHARACTER SET utf8 NOT NULL,
  `phone_code` VARCHAR(3) CHARACTER SET utf8 NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_code_2` (`iso_code_2`),
  UNIQUE KEY `iso_code_3` (`iso_code_3`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_currencies` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL,
  `code` VARCHAR(3) NOT NULL,
  `number` VARCHAR(3) NOT NULL,
  `name` VARCHAR(32) NOT NULL,
  `value` DECIMAL(10,4) NOT NULL,
  `decimals` TINYINT(1) NOT NULL,
  `prefix` VARCHAR(8) NOT NULL,
  `suffix` VARCHAR(8) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `email` VARCHAR(128) NOT NULL,
  `password` VARCHAR(128) NOT NULL,
  `tax_id` VARCHAR(32) NOT NULL,
  `company` VARCHAR(64) NOT NULL,
  `firstname` VARCHAR(64) NOT NULL,
  `lastname` VARCHAR(64) NOT NULL,
  `address1` VARCHAR(64) NOT NULL,
  `address2` VARCHAR(64) NOT NULL,
  `postcode` VARCHAR(8) NOT NULL,
  `city` VARCHAR(32) NOT NULL,
  `country_code` VARCHAR(4) NOT NULL,
  `zone_code` VARCHAR(8) NOT NULL,
  `phone` VARCHAR(24) NOT NULL,
  `different_shipping_address` TINYINT(1) NOT NULL,
  `shipping_company` VARCHAR(64) NOT NULL,
  `shipping_firstname` VARCHAR(64) NOT NULL,
  `shipping_lastname` VARCHAR(64) NOT NULL,
  `shipping_address1` VARCHAR(64) NOT NULL,
  `shipping_address2` VARCHAR(64) NOT NULL,
  `shipping_city` VARCHAR(32) NOT NULL,
  `shipping_postcode` VARCHAR(8) NOT NULL,
  `shipping_country_code` VARCHAR(4) NOT NULL,
  `shipping_zone_code` VARCHAR(8) NOT NULL,
  `shipping_phone` VARCHAR(24) NOT NULL,
  `newsletter` TINYINT(1) NOT NULL DEFAULT '1',
  `notes` TEXT NOT NULL,
  `password_reset_token` VARCHAR(128) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `delivery_status_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `delivery_status` (`delivery_status_id`, `language_code`),
  KEY `delivery_status_id` (`delivery_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_geo_zones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` VARCHAR(256) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_languages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL,
  `code` VARCHAR(2) NOT NULL,
  `code2` VARCHAR(3) NOT NULL,
  `name` VARCHAR(32) NOT NULL,
  `locale` VARCHAR(32) NOT NULL,
  `charset` VARCHAR(16) NOT NULL,
  `raw_date` VARCHAR(32) NOT NULL,
  `raw_time` VARCHAR(32) NOT NULL,
  `raw_datetime` VARCHAR(32) NOT NULL,
  `format_date` VARCHAR(32) NOT NULL,
  `format_time` VARCHAR(32) NOT NULL,
  `format_datetime` VARCHAR(32) NOT NULL,
  `decimal_point` VARCHAR(1) NOT NULL,
  `thousands_sep` VARCHAR(1) NOT NULL,
  `currency_code` VARCHAR(3) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY `id` (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_manufacturers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(4) NOT NULL,
  `code` VARCHAR(32) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `keywords` VARCHAR(256) NOT NULL,
  `image` VARCHAR(256) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_manufacturers_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `manufacturer_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `short_description` VARCHAR(256) NOT NULL,
  `description` TEXT NOT NULL,
  `h1_title` VARCHAR(128) NOT NULL,
  `head_title` VARCHAR(128) NOT NULL,
  `meta_description` VARCHAR(256) NOT NULL,
  `link` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `manufacturer` (`manufacturer_id`, `language_code`),
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_modules` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `module_id` VARCHAR(64) NOT NULL,
  `type` VARCHAR(16) NOT NULL,
  `status` TINYINT(1) NOT NULL,
  `priority` TINYINT(4) NOT NULL,
  `settings` TEXT NOT NULL,
  `last_log` TEXT NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `module_id` (`module_id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_option_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `function` VARCHAR(32) NOT NULL,
  `required` TINYINT(1) NOT NULL,
  `sort` VARCHAR(32) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_option_groups_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `description` VARCHAR(512) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_option_values` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) NOT NULL,
  `value` VARCHAR(128) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_option_values_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `value_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `value_id` (`value_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uid` VARCHAR(13) NOT NULL,
  `order_status_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `customer_company` VARCHAR(64) NOT NULL,
  `customer_firstname` VARCHAR(64) NOT NULL,
  `customer_lastname` VARCHAR(64) NOT NULL,
  `customer_email` VARCHAR(128) NOT NULL,
  `customer_phone` VARCHAR(24) NOT NULL,
  `customer_tax_id` VARCHAR(32) NOT NULL,
  `customer_address1` VARCHAR(64) NOT NULL,
  `customer_address2` VARCHAR(64) NOT NULL,
  `customer_city` VARCHAR(32) NOT NULL,
  `customer_postcode` VARCHAR(8) NOT NULL,
  `customer_country_code` VARCHAR(2) NOT NULL,
  `customer_zone_code` VARCHAR(8) NOT NULL,
  `shipping_company` VARCHAR(64) NOT NULL,
  `shipping_firstname` VARCHAR(64) NOT NULL,
  `shipping_lastname` VARCHAR(64) NOT NULL,
  `shipping_address1` VARCHAR(64) NOT NULL,
  `shipping_address2` VARCHAR(64) NOT NULL,
  `shipping_city` VARCHAR(32) NOT NULL,
  `shipping_postcode` VARCHAR(8) NOT NULL,
  `shipping_country_code` VARCHAR(2) NOT NULL,
  `shipping_zone_code` VARCHAR(8) NOT NULL,
  `shipping_phone` VARCHAR(24) NOT NULL,
  `shipping_option_id` VARCHAR(32) NOT NULL,
  `shipping_option_name` VARCHAR(64) NOT NULL,
  `shipping_tracking_id` VARCHAR(128) NOT NULL,
  `payment_option_id` VARCHAR(32) NOT NULL,
  `payment_option_name` VARCHAR(64) NOT NULL,
  `payment_transaction_id` VARCHAR(128) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `weight_total` DECIMAL(11,4) NOT NULL,
  `weight_class` VARCHAR(2) NOT NULL,
  `currency_code` VARCHAR(3) NOT NULL,
  `currency_value` DECIMAL(11,4)NOT NULL,
  `payment_due` DECIMAL(11,4) NOT NULL,
  `tax_total` DECIMAL(11,4) NOT NULL,
  `client_ip` VARCHAR(39) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_id` (`order_status_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `author` enum('system','staff','customer') NOT NULL,
  `text` VARCHAR(512) NOT NULL,
  `hidden` TINYINT(1) NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `option_stock_combination` VARCHAR(32) NOT NULL,
  `options` VARCHAR(4096) NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `sku` VARCHAR(64) NOT NULL,
  `quantity` DECIMAL(11,4) NOT NULL,
  `price` DECIMAL(11,4) NOT NULL,
  `tax` DECIMAL(11,4) NOT NULL,
  `weight` DECIMAL(11,4) NOT NULL,
  `weight_class` VARCHAR(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(24) NOT NULL,
  `color` VARCHAR(7) NOT NULL,
  `is_sale` TINYINT(1) NOT NULL,
  `is_archived` TINYINT(1) NOT NULL,
  `notify` TINYINT(1) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_status_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` VARCHAR(256) NOT NULL,
  `email_subject` VARCHAR(128) NOT NULL,
  `email_message` VARCHAR(2048) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_id` (`order_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_totals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `module_id` VARCHAR(32) NOT NULL,
  `title` VARCHAR(128) NOT NULL,
  `value` float NOT NULL,
  `tax` float NOT NULL,
  `calculate` TINYINT(1) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_pages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL,
  `dock` VARCHAR(64) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `dock` (`dock`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_pages_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `page_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `title` VARCHAR(256) NOT NULL,
  `content` mediumtext NOT NULL,
  `head_title` VARCHAR(128) NOT NULL,
  `meta_description` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL,
  `manufacturer_id` INT(11) NOT NULL,
  `supplier_id` INT(11) NOT NULL,
  `delivery_status_id` INT(11) NOT NULL,
  `sold_out_status_id` INT(11) NOT NULL,
  `default_category_id` INT(11) NOT NULL,
  `product_groups` VARCHAR(128) NOT NULL,
  `keywords` VARCHAR(256) NOT NULL,
  `code` VARCHAR(64) NOT NULL,
  `sku` VARCHAR(64) NOT NULL,
  `upc` VARCHAR(24) NOT NULL COMMENT 'Deprecated',
  `gtin` VARCHAR(32) NOT NULL,
  `taric` VARCHAR(16) NOT NULL,
  `quantity` DECIMAL(11,4) NOT NULL,
  `quantity_unit_id` INT(1) NOT NULL,
  `weight` DECIMAL(10,4) NOT NULL,
  `weight_class` VARCHAR(2) NOT NULL,
  `dim_x` DECIMAL(10,4) NOT NULL,
  `dim_y` DECIMAL(10,4) NOT NULL,
  `dim_z` DECIMAL(10,4) NOT NULL,
  `dim_class` VARCHAR(2) NOT NULL,
  `purchase_price` DECIMAL(10,4) NOT NULL,
  `purchase_price_currency_code` VARCHAR(3) NOT NULL,
  `tax_class_id` INT(11) NOT NULL,
  `image` VARCHAR(256) NOT NULL,
  `views` INT(11) NOT NULL,
  `purchases` INT(11) NOT NULL,
  `date_valid_from` date NOT NULL,
  `date_valid_to` date NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `default_category_id` (`default_category_id`),
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `keywords` (`keywords`),
  KEY `code` (`code`),
  KEY `date_valid_from` (`date_valid_from`),
  KEY `date_valid_to` (`date_valid_to`),
  KEY `purchases` (`purchases`),
  KEY `views` (`views`),
  KEY `product_groups` (`product_groups`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_campaigns` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `USD` DECIMAL(11,4) NOT NULL,
  `EUR` DECIMAL(11,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_product_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(4) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_product_groups_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_group_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_group_id` (`product_group_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_product_groups_values` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_group_id` INT(11) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_group_id` (`product_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_product_groups_values_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_group_value_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_group_value_id` (`product_group_value_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `filename` VARCHAR(256) NOT NULL,
  `checksum` CHAR(32) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `short_description` VARCHAR(256) NOT NULL,
  `description` TEXT NOT NULL,
  `head_title` VARCHAR(128) NOT NULL,
  `meta_description` VARCHAR(256) NOT NULL,
  `attributes` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_options` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `group_id` INT(11) NOT NULL,
  `value_id` INT(11) NOT NULL,
  `price_operator` VARCHAR(1) NOT NULL,
  `USD` DECIMAL(11,4) NOT NULL,
  `EUR` DECIMAL(11,4) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_options_stock` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `combination` VARCHAR(64) NOT NULL,
  `sku` VARCHAR(64) NOT NULL,
  `weight` DECIMAL(11,4) NOT NULL,
  `weight_class` VARCHAR(2) NOT NULL,
  `dim_x` DECIMAL(11,4) NOT NULL,
  `dim_y` DECIMAL(11,4) NOT NULL,
  `dim_z` DECIMAL(11,4) NOT NULL,
  `dim_class` VARCHAR(2) NOT NULL,
  `quantity` DECIMAL(11,4) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_prices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `USD` DECIMAL(11,4) NOT NULL,
  `EUR` DECIMAL(11,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_to_categories` (
   `product_id` INT(11) NOT NULL,
   `category_id` INT(11) NOT NULL,
   PRIMARY KEY(`product_id`, `category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `decimals` TINYINT(1) NOT NULL,
  `separate` TINYINT(1) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quantity_unit_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(32) NOT NULL,
  `description` VARCHAR(512),
  PRIMARY KEY (`id`),
  KEY `quantity_unit_id` (`quantity_unit_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_group_key` VARCHAR(64) NOT NULL,
  `type` enum('global','local') NOT NULL DEFAULT 'local',
  `title` VARCHAR(128) NOT NULL,
  `description` VARCHAR(512) NOT NULL,
  `key` VARCHAR(64) NOT NULL,
  `value` VARCHAR(8192) NOT NULL,
  `function` VARCHAR(128) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `setting_group_key` (`setting_group_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(64) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` VARCHAR(256) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_slides` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL,
  `languages` VARCHAR(32) NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `image` VARCHAR(64) NOT NULL,
  `priority` TINYINT(2) NOT NULL,
  `date_valid_from` DATETIME NOT NULL,
  `date_valid_to` DATETIME NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_sold_out_statuses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `orderable` TINYINT(1) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_sold_out_statuses_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sold_out_status_id` INT(11) NOT NULL,
  `language_code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sold_out_status_id` (`sold_out_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_suppliers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` TEXT NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  `phone` VARCHAR(24) NOT NULL,
  `link` VARCHAR(256) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_classes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` VARCHAR(64) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_rates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tax_class_id` INT(11) NOT NULL,
  `geo_zone_id` INT(11) NOT NULL,
  `code` VARCHAR(32) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` VARCHAR(128) NOT NULL,
  `rate` DECIMAL(10,4) NOT NULL,
  `type` enum('fixed','percent') NOT NULL DEFAULT 'percent',
  `address_type` ENUM('shipping','payment') NOT NULL DEFAULT 'shipping',
  `customer_type` enum('individuals','companies','both') NOT NULL DEFAULT 'both',
  `tax_id_rule` enum('with','without','both') NOT NULL DEFAULT 'both',
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tax_class_id` (`tax_class_id`),
  KEY `geo_zone_id` (`geo_zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_translations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(250) NOT NULL,
  `text_en` TEXT NOT NULL,
  `html` TINYINT(1) NOT NULL,
  `pages` TEXT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_accessed` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL,
  `username` VARCHAR(32) NOT NULL,
  `password` VARCHAR(128) NOT NULL,
  `permissions` VARCHAR(4096) NOT NULL,
  `last_ip` VARCHAR(15) NOT NULL,
  `last_host` VARCHAR(64) NOT NULL,
  `login_attempts` INT(11) NOT NULL,
  `total_logins` INT(11) NOT NULL,
  `date_blocked` DATETIME NOT NULL,
  `date_expires` DATETIME NOT NULL,
  `date_active` DATETIME NOT NULL,
  `date_login` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  `date_updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `country_code` VARCHAR(4) NOT NULL,
  `code` VARCHAR(8) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`),
  KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones_to_geo_zones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `geo_zone_id` INT(11) NOT NULL,
  `country_code` VARCHAR(2) NOT NULL,
  `zone_code` VARCHAR(8) NOT NULL,
  `date_updated` DATETIME NOT NULL,
  `date_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `geo_zone_id` (`geo_zone_id`),
  KEY `country_code` (`country_code`),
  KEY `zone_code` (`zone_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
