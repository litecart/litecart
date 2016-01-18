CREATE TABLE `lc_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `company` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `address1` varchar(64) NOT NULL,
  `address2` varchar(64) NOT NULL,
  `city` varchar(32) NOT NULL,
  `postcode` varchar(8) NOT NULL,
  `country_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `cart_uid` VARCHAR(13) NOT NULL,
  `key` varchar(32) NOT NULL,
  `product_id` int(11) NOT NULL,
  `options` varchar(2048) NOT NULL,
  `quantity` decimal(11, 4) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `cart_uid` (`cart_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `google_taxonomy_id` INT(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `code` varchar(64) NOT NULL,
  `list_style` varchar(32) NOT NULL,
  `dock` varchar(32) NOT NULL,
  `keywords` varchar(256) NOT NULL,
  `image` varchar(256) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`),
  KEY `dock` (`dock`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(128) NOT NULL,
  `short_description` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `head_title` varchar(128) NOT NULL,
  `h1_title` varchar(128) NOT NULL,
  `meta_description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `name` VARCHAR(64) NOT NULL,
  `domestic_name` varchar(64) CHARACTER SET utf8 NOT NULL,
  `iso_code_1` varchar(3) NOT NULL,
  `iso_code_2` varchar(2) NOT NULL,
  `iso_code_3` varchar(3) NOT NULL,
  `tax_id_format` varchar(64) NOT NULL,
  `address_format` varchar(128) NOT NULL,
  `postcode_format` varchar(512) NOT NULL,
  `postcode_required` tinyint(1) NOT NULL,
  `language_code` varchar(2) CHARACTER SET utf8 NOT NULL,
  `currency_code` varchar(3) CHARACTER SET utf8 NOT NULL,
  `phone_code` varchar(3) CHARACTER SET utf8 NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_code_2` (`iso_code_2`),
  UNIQUE KEY `iso_code_3` (`iso_code_3`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `code` varchar(3) NOT NULL,
  `number` varchar(3) NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` decimal(10,4) NOT NULL,
  `decimals` tinyint(1) NOT NULL,
  `prefix` varchar(8) NOT NULL,
  `suffix` varchar(8) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `tax_id` varchar(32) NOT NULL,
  `company` varchar(64) NOT NULL,
  `firstname` varchar(64) NOT NULL,
  `lastname` varchar(64) NOT NULL,
  `address1` varchar(64) NOT NULL,
  `address2` varchar(64) NOT NULL,
  `postcode` varchar(8) NOT NULL,
  `city` varchar(32) NOT NULL,
  `country_code` varchar(4) NOT NULL,
  `zone_code` varchar(8) NOT NULL,
  `phone` varchar(24) NOT NULL,
  `mobile` varchar(24) NOT NULL,
  `different_shipping_address` tinyint(1) NOT NULL,
  `shipping_company` varchar(64) NOT NULL,
  `shipping_firstname` varchar(64) NOT NULL,
  `shipping_lastname` varchar(64) NOT NULL,
  `shipping_address1` varchar(64) NOT NULL,
  `shipping_address2` varchar(64) NOT NULL,
  `shipping_city` varchar(32) NOT NULL,
  `shipping_postcode` varchar(8) NOT NULL,
  `shipping_country_code` varchar(4) NOT NULL,
  `shipping_zone_code` varchar(8) NOT NULL,
  `newsletter` tinyint(1) NOT NULL DEFAULT '1',
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_status_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_status_id` (`delivery_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_geo_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(256) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `code` varchar(2) NOT NULL,
  `code2` varchar(3) NOT NULL,
  `name` varchar(32) NOT NULL,
  `locale` varchar(32) NOT NULL,
  `charset` varchar(16) NOT NULL,
  `raw_date` varchar(32) NOT NULL,
  `raw_time` varchar(32) NOT NULL,
  `raw_datetime` varchar(32) NOT NULL,
  `format_date` varchar(32) NOT NULL,
  `format_time` varchar(32) NOT NULL,
  `format_datetime` varchar(32) NOT NULL,
  `decimal_point` varchar(1) NOT NULL,
  `thousands_sep` varchar(1) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_manufacturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL,
  `code` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `keywords` varchar(256) NOT NULL,
  `image` varchar(256) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_manufacturers_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturer_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `short_description` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `h1_title` varchar(128) NOT NULL,
  `head_title` varchar(128) NOT NULL,
  `meta_description` varchar(256) NOT NULL,
  `link` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_option_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `function` varchar(32) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `sort` varchar(32) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_option_groups_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(128) NOT NULL,
  `description` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_option_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `value` varchar(128) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_option_values_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `value_id` (`value_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_company` varchar(64) NOT NULL,
  `customer_firstname` varchar(64) NOT NULL,
  `customer_lastname` varchar(64) NOT NULL,
  `customer_email` varchar(128) NOT NULL,
  `customer_phone` varchar(24) NOT NULL,
  `customer_mobile` varchar(24) NOT NULL,
  `customer_tax_id` varchar(32) NOT NULL,
  `customer_address1` varchar(64) NOT NULL,
  `customer_address2` varchar(64) NOT NULL,
  `customer_city` varchar(32) NOT NULL,
  `customer_postcode` varchar(8) NOT NULL,
  `customer_country_code` varchar(2) NOT NULL,
  `customer_zone_code` varchar(8) NOT NULL,
  `shipping_company` varchar(64) NOT NULL,
  `shipping_firstname` varchar(64) NOT NULL,
  `shipping_lastname` varchar(64) NOT NULL,
  `shipping_address1` varchar(64) NOT NULL,
  `shipping_address2` varchar(64) NOT NULL,
  `shipping_city` varchar(32) NOT NULL,
  `shipping_postcode` varchar(8) NOT NULL,
  `shipping_country_code` varchar(2) NOT NULL,
  `shipping_zone_code` varchar(8) NOT NULL,
  `shipping_option_id` varchar(32) NOT NULL,
  `shipping_option_name` varchar(64) NOT NULL,
  `shipping_tracking_id` varchar(128) NOT NULL,
  `payment_option_id` varchar(32) NOT NULL,
  `payment_option_name` varchar(64) NOT NULL,
  `payment_transaction_id` varchar(128) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `weight_total` decimal(11,4) NOT NULL,
  `weight_class` varchar(2) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `currency_value` decimal(11,4)NOT NULL,
  `payment_due` decimal(11,4) NOT NULL,
  `tax_total` decimal(11,4) NOT NULL,
  `client_ip` varchar(39) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_id` (`order_status_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `author` enum('system','staff','customer') NOT NULL,
  `text` varchar(512) NOT NULL,
  `hidden` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `option_stock_combination` varchar(32) NOT NULL,
  `options` varchar(512) NOT NULL,
  `name` varchar(128) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `quantity` decimal(11,4) NOT NULL,
  `price` decimal(11,4) NOT NULL,
  `tax` decimal(11,4) NOT NULL,
  `weight` decimal(11,4) NOT NULL,
  `weight_class` varchar(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` varchar(24) NOT NULL,
  `color` varchar(7) NOT NULL,
  `is_sale` tinyint(1) NOT NULL,
  `notify` tinyint(1) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_status_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(256) NOT NULL,
  `email_message` VARCHAR(2048) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_id` (`order_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_totals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `module_id` varchar(32) NOT NULL,
  `title` varchar(128) NOT NULL,
  `value` float NOT NULL,
  `tax` float NOT NULL,
  `calculate` tinyint(1) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `dock` VARCHAR(64) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `dock` (`dock`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_pages_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `title` varchar(256) NOT NULL,
  `content` text NOT NULL,
  `head_title` varchar(128) NOT NULL,
  `meta_description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `delivery_status_id` int(11) NOT NULL,
  `sold_out_status_id` int(11) NOT NULL,
  `default_category_id` int(11) NOT NULL,
  `product_groups` varchar(128) NOT NULL,
  `keywords` varchar(256) NOT NULL,
  `code` varchar(64) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `upc` varchar(24) NOT NULL COMMENT 'Deprecated',
  `gtin` varchar(32) NOT NULL,
  `taric` varchar(16) NOT NULL,
  `quantity` decimal(11,4) NOT NULL,
  `quantity_unit_id` INT(1) NOT NULL,
  `weight` decimal(10,4) NOT NULL,
  `weight_class` varchar(2) NOT NULL,
  `dim_x` decimal(10,4) NOT NULL,
  `dim_y` decimal(10,4) NOT NULL,
  `dim_z` decimal(10,4) NOT NULL,
  `dim_class` varchar(2) NOT NULL,
  `purchase_price` decimal(10,4) NOT NULL,
  `purchase_price_currency_code` varchar(3) NOT NULL,
  `tax_class_id` int(11) NOT NULL,
  `image` varchar(256) NOT NULL,
  `views` int(11) NOT NULL,
  `purchases` int(11) NOT NULL,
  `date_valid_from` date NOT NULL,
  `date_valid_to` date NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `USD` decimal(11,4) NOT NULL,
  `EUR` decimal(11,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_product_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_product_groups_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_group_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_group_id` (`product_group_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_product_groups_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_group_id` int(11) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_group_id` (`product_group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_product_groups_values_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_group_value_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_group_value_id` (`product_group_value_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `filename` varchar(256) NOT NULL,
  `checksum` CHAR(32) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(128) NOT NULL,
  `short_description` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `head_title` varchar(128) NOT NULL,
  `meta_description` varchar(256) NOT NULL,
  `attributes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL,
  `price_operator` varchar(1) NOT NULL,
  `USD` decimal(11,4) NOT NULL,
  `EUR` decimal(11,4) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_options_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `combination` varchar(64) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `weight` decimal(11,4) NOT NULL,
  `weight_class` varchar(2) NOT NULL,
  `dim_x` decimal(11,4) NOT NULL,
  `dim_y` decimal(11,4) NOT NULL,
  `dim_z` decimal(11,4) NOT NULL,
  `dim_class` varchar(2) NOT NULL,
  `quantity` decimal(11,4) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `USD` decimal(11,4) NOT NULL,
  `EUR` decimal(11,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_to_categories` (
   `product_id` int(11) NOT NULL,
   `category_id` int(11) NOT NULL,
   PRIMARY KEY(`product_id`, `category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `decimals` tinyint(1) NOT NULL,
  `separate` tinyint(1) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantity_unit_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` VARCHAR(512),
  PRIMARY KEY (`id`),
  KEY `quantity_unit_id` (`quantity_unit_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_group_key` varchar(64) NOT NULL,
  `type` enum('global','local') NOT NULL DEFAULT 'local',
  `title` varchar(128) NOT NULL,
  `description` varchar(512) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` varchar(2048) NOT NULL,
  `function` varchar(128) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `setting_group_key` (`setting_group_key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(256) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `language_code` varchar(8) NOT NULL,
  `name` varchar(128) NOT NULL,
  `caption` varchar(512) NOT NULL,
  `link` varchar(256) NOT NULL,
  `image` varchar(64) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_valid_from` datetime NOT NULL,
  `date_valid_to` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_sold_out_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderable` tinyint(1) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_sold_out_statuses_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sold_out_status_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sold_out_status_id` (`sold_out_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `email` varchar(128) NOT NULL,
  `phone` varchar(24) NOT NULL,
  `link` varchar(256) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(64) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_class_id` int(11) NOT NULL,
  `geo_zone_id` int(11) NOT NULL,
  `code` VARCHAR(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(128) NOT NULL,
  `rate` decimal(10,4) NOT NULL,
  `type` enum('fixed','percent') NOT NULL DEFAULT 'percent',
  `customer_type` enum('individuals','companies','both') NOT NULL DEFAULT 'both',
  `tax_id_rule` enum('with','without','both') NOT NULL DEFAULT 'both',
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tax_class_id` (`tax_class_id`),
  KEY `geo_zone_id` (`geo_zone_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(250) NOT NULL,
  `text_en` text NOT NULL,
  `html` tinyint(1) NOT NULL,
  `pages` text NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_accessed` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(128) NOT NULL,
  `last_ip` varchar(15) NOT NULL,
  `last_host` varchar(64) NOT NULL,
  `login_attempts` int(11) NOT NULL,
  `total_logins` int(11) NOT NULL,
  `date_blocked` datetime NOT NULL,
  `date_expires` datetime NOT NULL,
  `date_active` datetime NOT NULL,
  `date_login` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(4) NOT NULL,
  `code` varchar(8) NOT NULL,
  `name` varchar(64) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones_to_geo_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `geo_zone_id` int(11) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `zone_code` varchar(8) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `geo_zone_id` (`geo_zone_id`),
  KEY `country_code` (`country_code`),
  KEY `zone_code` (`zone_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};
