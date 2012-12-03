DROP TABLE IF EXISTS `lc_addresses`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_addresses` (
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_categories`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `code` VARCHAR(64) NOT NULL,
  `image` varchar(64) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_categories_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_categories_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(128) NOT NULL,
  `short_description` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `keywords` varchar(256) NOT NULL,
  `head_title` varchar(128) NOT NULL,
  `meta_description` varchar(256) NOT NULL,
  `meta_keywords` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_countries`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(64) NOT NULL,
  `domestic_name` varchar(64) NOT NULL,
  `iso_code_2` varchar(2) NOT NULL DEFAULT '',
  `iso_code_3` varchar(3) NOT NULL DEFAULT '',
  `address_format` text NOT NULL,
  `postcode_required` tinyint(1) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `phone_code` varchar(3) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_currencies`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `code` varchar(3) NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` decimal(10,4) NOT NULL,
  `decimals` tinyint(1) NOT NULL,
  `prefix` varchar(8) NOT NULL,
  `suffix` varchar(8) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_customers`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_delivery_status`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_delivery_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_delivery_status_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_delivery_status_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_status_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_geo_zones`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_geo_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(256) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_languages`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `code` varchar(2) NOT NULL,
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
  `currency_code` VARCHAR(3) NOT NULL, 
  `priority` tinyint(4) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_manufacturers`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_manufacturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `image` varchar(64) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_manufacturers_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_manufacturers_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturer_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  `short_description` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `keywords` varchar(256) NOT NULL,
  `head_title` varchar(128) NOT NULL,
  `meta_description` varchar(256) NOT NULL,
  `meta_keywords` varchar(256) NOT NULL,
  `link` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_orders`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `customer_country_name` varchar(64) NOT NULL,
  `customer_zone_code` varchar(8) NOT NULL,
  `customer_zone_name` varchar(32) NOT NULL,
  `shipping_company` varchar(64) NOT NULL,
  `shipping_firstname` varchar(64) NOT NULL,
  `shipping_lastname` varchar(64) NOT NULL,
  `shipping_address1` varchar(64) NOT NULL,
  `shipping_address2` varchar(64) NOT NULL,
  `shipping_city` varchar(32) NOT NULL,
  `shipping_postcode` varchar(8) NOT NULL,
  `shipping_country_code` varchar(2) NOT NULL,
  `shipping_country_name` varchar(64) NOT NULL,
  `shipping_zone_code` varchar(8) NOT NULL,
  `shipping_zone_name` varchar(32) NOT NULL,
  `shipping_option_id` varchar(32) NOT NULL,
  `shipping_option_name` varchar(64) NOT NULL,
  `payment_option_id` varchar(32) NOT NULL,
  `payment_option_name` varchar(64) NOT NULL,
  `payment_transaction_id` varchar(128) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `weight` decimal(11,4) NOT NULL,
  `weight_class` varchar(2) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `currency_value` float NOT NULL,
  `payment_due` float NOT NULL,
  `tax_total` float NOT NULL,
  `client_ip` varchar(39) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_orders_items`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_orders_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` varchar(32) NOT NULL,
  `option_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `model` varchar(64) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `upc` varchar(12) NOT NULL,
  `taric` varchar(16) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(11,4) NOT NULL,
  `tax` decimal(11,4) NOT NULL,
  `tax_class_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_orders_status`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_orders_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_sale` tinyint(1) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_orders_status_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_orders_status_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_status_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_orders_tax`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_orders_tax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `tax_rate_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `tax` decimal(11,4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_orders_totals`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_orders_totals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `title` varchar(128) NOT NULL,
  `value` float NOT NULL,
  `tax` float NOT NULL,
  `tax_class_id` int(11) NOT NULL,
  `calculate` tinyint(1) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_pages`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dock_menu` tinyint(1) NOT NULL,
  `dock_support` tinyint(1) NOT NULL,
  `priority` int(11) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_pages_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_pages_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `title` varchar(256) NOT NULL,
  `content` text NOT NULL,
  `head_title` varchar(128) NOT NULL,
  `meta_description` varchar(256) NOT NULL,
  `meta_keywords` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_products`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `delivery_status_id` int(11) NOT NULL,
  `sold_out_status_id` int(11) NOT NULL,
  `categories` varchar(64) NOT NULL,
  `product_groups` varchar(32) NOT NULL,
  `model` varchar(64) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `upc` varchar(12) NOT NULL,
  `taric` varchar(16) NOT NULL,
  `quantity` int(11) NOT NULL,
  `weight` decimal(10,4) NOT NULL,
  `weight_class` varchar(2) NOT NULL,
  `dim_x` decimal(10,4) NOT NULL,
  `dim_y` decimal(10,4) NOT NULL,
  `dim_z` decimal(10,4) NOT NULL,
  `dim_class` varchar(2) NOT NULL,
  `purchase_price` decimal(10,4) NOT NULL,
  `price` decimal(10,4) NOT NULL,
  `specials_price` decimal(10,4) NOT NULL,
  `specials_expire` datetime NOT NULL,
  `tax_class_id` int(11) NOT NULL,
  `image` varchar(64) NOT NULL,
  `views` int(11) NOT NULL,
  `purchases` int(11) NOT NULL,
  `date_available` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_products_configurations`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_products_configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `combination` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_products_images`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_products_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_products_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_products_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(128) NOT NULL,
  `short_description` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `head_title` varchar(128) NOT NULL,
  `meta_description` varchar(256) NOT NULL,
  `meta_keywords` varchar(256) NOT NULL,
  `attributes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_products_options`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_products_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `combination` varchar(64) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `price_operator` varchar(16) NOT NULL,
  `price` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `weight_class` varchar(2) NOT NULL,
  `dim_x` decimal(11,4) NOT NULL,
  `dim_y` decimal(11,4) NOT NULL,
  `dim_z` decimal(11,4) NOT NULL,
  `dim_class` varchar(2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_product_groups`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_product_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_product_groups_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_product_groups_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_group_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_product_groups_values`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_product_groups_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_group_id` int(11) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_product_groups_values_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_product_groups_values_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_group_value_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_product_option_groups`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_product_option_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_product_option_groups_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_product_option_groups_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_product_option_values`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_product_option_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_product_option_values_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_product_option_values_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_seo_links_cache`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_seo_links_cache` (
  `uri` varchar(256) NOT NULL,
  `seo_uri` varchar(256) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  KEY `seo_uri` (`seo_uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_settings`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('global','local') NOT NULL DEFAULT 'local',
  `setting_group_key` varchar(64) NOT NULL,
  `key` varchar(64) NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` varchar(512) NOT NULL,
  `value` varchar(512) NOT NULL,
  `function` varchar(128) NOT NULL,
  `priority` int(11) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_settings_groups`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_settings_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(256) NOT NULL,
  `priority` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_sold_out_status`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_sold_out_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderable` tinyint(1) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_sold_out_status_info`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_sold_out_status_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sold_out_status_id` int(11) NOT NULL,
  `language_code` varchar(2) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_suppliers`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `email` varchar(128) NOT NULL,
  `phone` varchar(24) NOT NULL,
  `link` varchar(256) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_tax_classes`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_tax_classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(64) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_tax_rates`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_tax_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_class_id` int(11) NOT NULL,
  `geo_zone_id` int(11) NOT NULL,
  `type` enum('fixed','percent') NOT NULL DEFAULT 'percent',
  `name` varchar(64) NOT NULL,
  `description` varchar(128) NOT NULL,
  `rate` decimal(10,4) NOT NULL,
  `customer_type` enum('individuals','companies','both') NOT NULL DEFAULT 'both',
  `tax_id_rule` enum('with','without','both') NOT NULL DEFAULT 'both',
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_translations`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `text_en` text NOT NULL,
  `html` tinyint(1) NOT NULL,
  `pages` text NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_accessed` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_zones`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(4) NOT NULL,
  `code` varchar(8) NOT NULL,
  `name` varchar(64) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_zones_to_geo_zones`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_zones_to_geo_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `geo_zone_id` int(11) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `zone_code` varchar(8) NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
