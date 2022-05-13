CREATE TABLE IF NOT EXISTS `lc_attribute_groups` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `sort` ENUM('alphabetical','priority') NOT NULL DEFAULT 'alphabetical',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_attribute_groups_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_group` (`group_id`,`language_code`),
  KEY `group_id` (`group_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_attribute_values` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_attribute_values_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `value_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_value` (`value_id`,`language_code`),
  KEY `value_id` (`value_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_banners` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_brands` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_brands_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `brand_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `short_description` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL DEFAULT '',
  `h1_title` VARCHAR(128) NOT NULL DEFAULT '',
  `head_title` VARCHAR(128) NOT NULL DEFAULT '',
  `meta_description` VARCHAR(512) NOT NULL DEFAULT '',
  `link` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_info` (`brand_id`, `language_code`),
  KEY `brand_id` (`brand_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `google_taxonomy_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `code` VARCHAR(64) NOT NULL DEFAULT '',
  `keywords` VARCHAR(255) NOT NULL DEFAULT '',
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_categories_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `short_description` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL DEFAULT '',
  `head_title` VARCHAR(128) NOT NULL DEFAULT '',
  `h1_title` VARCHAR(128) NOT NULL DEFAULT '',
  `meta_description` VARCHAR(512) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category` (`category_id`, `language_code`),
  KEY `category_id` (`category_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_countries` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `domestic_name` VARCHAR(64) NOT NULL DEFAULT '',
  `iso_code_1` CHAR(3) NOT NULL DEFAULT '',
  `iso_code_2` CHAR(2) NOT NULL DEFAULT '',
  `iso_code_3` CHAR(3) NOT NULL DEFAULT '',
  `tax_id_format` VARCHAR(64) NOT NULL DEFAULT '',
  `address_format` VARCHAR(128) NOT NULL DEFAULT '',
  `postcode_format` VARCHAR(255) NOT NULL DEFAULT '',
  `postcode_required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `currency_code` CHAR(3) NOT NULL DEFAULT '',
  `phone_code` VARCHAR(3) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_code_1` (`iso_code_1`),
  UNIQUE KEY `iso_code_2` (`iso_code_2`),
  UNIQUE KEY `iso_code_3` (`iso_code_3`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_currencies` (
  `id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `code` CHAR(3) NOT NULL DEFAULT '',
  `number` CHAR(3) NOT NULL DEFAULT '',
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_customers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `email` VARCHAR(128) NOT NULL DEFAULT '',
  `password_hash` VARCHAR(255) NOT NULL DEFAULT '',
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
  `different_shipping_address` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `shipping_company` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address1` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address2` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_city` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_postcode` VARCHAR(16) NOT NULL DEFAULT '',
  `shipping_country_code` CHAR(2) NOT NULL DEFAULT '',
  `shipping_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `shipping_phone` VARCHAR(24) NOT NULL DEFAULT '',
  `notes` TEXT NOT NULL DEFAULT '',
  `password_reset_token` VARCHAR(128) NOT NULL DEFAULT '',
  `login_attempts` INT NOT NULL DEFAULT '0',
  `total_logins` INT(11) UNSIGNED NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_delivery_statuses_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_status` (`delivery_status_id`, `language_code`),
  KEY `delivery_status_id` (`delivery_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_emails` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_geo_zones` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_languages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_newsletter_recipients` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(128) NOT NULL DEFAULT '',
  `client_ip` VARCHAR(64) NOT NULL DEFAULT '',
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `no` VARCHAR(1) NOT NULL DEFAULT '',
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
  `customer_country_code` CHAR(2) NOT NULL DEFAULT '',
  `customer_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `shipping_company` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address1` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address2` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_city` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_postcode` VARCHAR(16) NOT NULL DEFAULT '',
  `shipping_country_code` CHAR(2) NOT NULL DEFAULT '',
  `shipping_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `shipping_phone` VARCHAR(24) NOT NULL DEFAULT '',
  `shipping_option_id` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_option_name` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_option_userdata` VARCHAR(512) NOT NULL DEFAULT '',
  `shipping_option_fee` FLOAT(11,4) NOT NULL DEFAULT '0',
  `shipping_option_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `shipping_tracking_id` VARCHAR(128) NOT NULL DEFAULT '',
  `shipping_tracking_url` VARCHAR(255) NOT NULL DEFAULT '',
  `shipping_progress` TINYINT(3) NOT NULL DEFAULT '0',
  `shipping_current_status` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_current_location` VARCHAR(128) NOT NULL DEFAULT '',
  `payment_option_id` VARCHAR(32) NOT NULL DEFAULT '',
  `payment_option_name` VARCHAR(64) NOT NULL DEFAULT '',
  `payment_option_userdata` VARCHAR(512) NOT NULL DEFAULT '',
  `payment_option_fee` FLOAT(11,4) NOT NULL DEFAULT '0',
  `payment_option_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `payment_transaction_id` VARCHAR(128) NOT NULL DEFAULT '',
  `payment_receipt_url` VARCHAR(255) NOT NULL DEFAULT '',
  `payment_terms` VARCHAR(8) NOT NULL DEFAULT '',
  `incoterm` VARCHAR(3) NOT NULL DEFAULT '',
  `reference` VARCHAR(128) NOT NULL DEFAULT '',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `weight_total` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
  `currency_code` CHAR(3) NOT NULL DEFAULT '',
  `currency_value` FLOAT(11,6) UNSIGNED NOT NULL DEFAULT '0',
  `display_prices_including_tax` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `subtotal` FLOAT(11,4) NOT NULL DEFAULT '0',
  `subtotal_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `discount` FLOAT(11,4) NOT NULL DEFAULT '0',
  `discount_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `total` FLOAT(11,4) NOT NULL DEFAULT '0',
  `total_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `client_ip` VARCHAR(39) NOT NULL DEFAULT '',
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
  KEY `unread` (`unread`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_comments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `author` ENUM('system','staff','customer') NOT NULL DEFAULT 'system',
  `text` VARCHAR(512) NOT NULL DEFAULT '',
  `hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `stock_item_id` INT(11) NOT NULL DEFAULT '0',
  `options` VARCHAR(4096) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `userdata` VARCHAR(1024) NOT NULL DEFAULT '',
  `sku` VARCHAR(32) NOT NULL DEFAULT '',
  `gtin` VARCHAR(32) NOT NULL DEFAULT '',
  `taric` VARCHAR(32) NOT NULL DEFAULT '',
  `quantity` FLOAT(11,4) NOT NULL DEFAULT '0',
  `price` FLOAT(11,4) NOT NULL DEFAULT '0',
  `tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `tax_rate` FLOAT(4,2) NOT NULL DEFAULT '0',
  `discount` FLOAT(11,4) NOT NULL DEFAULT '0',
  `discount_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `sum` FLOAT(11,4) NOT NULL DEFAULT '0',
  `sum_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `weight` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
  `length` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `width` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `height` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
  `downloads` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `stock_item_id` (`stock_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_orders_totals` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `module_id` VARCHAR(32) NOT NULL DEFAULT '',
  `title` VARCHAR(128) NOT NULL DEFAULT '',
  `amount` FLOAT(11,4) NOT NULL DEFAULT '0',
  `tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `tax_rate` FLOAT(4,2) NOT NULL DEFAULT '0',
  `calculate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `state` ENUM('','created','on_hold','ready','delayed','processing','dispatched','in_transit','delivered','returning','returned','cancelled','fraud') NOT NULL DEFAULT '',
  `hidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `icon` VARCHAR(24) NOT NULL DEFAULT '',
  `color` VARCHAR(7) NOT NULL DEFAULT '',
  `is_sale` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `is_archived` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `is_trackable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `stock_action` ENUM('none','reserve','withdraw') NOT NULL DEFAULT 'none',
  `notify` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_sale` (`is_sale`),
  KEY `is_archived` (`is_archived`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_order_statuses_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `email_subject` VARCHAR(128) NOT NULL DEFAULT '',
  `email_message` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_status_info` (`order_status_id`, `language_code`),
  KEY `order_status_id` (`order_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_pages_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `content` MEDIUMTEXT NOT NULL DEFAULT '',
  `head_title` VARCHAR(128) NOT NULL DEFAULT '',
  `meta_description` VARCHAR(512) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_info` (`page_id`, `language_code`),
  KEY `page_id` (`page_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `brand_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `supplier_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `delivery_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `sold_out_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `default_category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `keywords` VARCHAR(255) NOT NULL DEFAULT '',
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `quantity` FLOAT(11,4) NOT NULL DEFAULT '0',
  `quantity_min` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `quantity_max` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `quantity_step` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `quantity_unit_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `recommended_price` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `tax_class_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `autofill_technical_data` TINYINT(1) NOT NULL DEFAULT '0',
  `views` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `purchases` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `date_valid_from` TIMESTAMP NULL DEFAULT NULL,
  `date_valid_to` TIMESTAMP NULL DEFAULT NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `default_category_id` (`default_category_id`),
  KEY `brand_id` (`brand_id`),
  KEY `keywords` (`keywords`),
  KEY `code` (`code`),
  KEY `date_valid_from` (`date_valid_from`),
  KEY `date_valid_to` (`date_valid_to`),
  KEY `purchases` (`purchases`),
  KEY `views` (`views`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_attributes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `value_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `custom_value` VARCHAR(255) NOT NULL DEFAULT '',
  `priority` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id` (`id`, `product_id`, `group_id`, `value_id`),
  KEY `product_id` (`product_id`),
  KEY `group_id` (`group_id`),
  KEY `value_id` (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_images` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `filename` VARCHAR(255) NOT NULL DEFAULT '',
  `checksum` CHAR(32) NULL,
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
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
  FULLTEXT KEY `description` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_prices` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `USD` FLOAT(11,4) NOT NULL DEFAULT '0',
  `EUR` FLOAT(11,4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_to_categories` (
   `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
   `category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
   PRIMARY KEY(`product_id`, `category_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_products_to_stock_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `stock_item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `price_operator` ENUM('+','%','*','=') NOT NULL DEFAULT '+',
  `USD` FLOAT(11,4) NOT NULL DEFAULT '0',
  `EUR` FLOAT(11,4) NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_option` (`product_id`, `stock_item_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `decimals` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `separate` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_quantity_units_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quantity_unit_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(32) NOT NULL DEFAULT '',
  `description` VARCHAR(512),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quantity_unit_info` (`quantity_unit_id`, `language_code`),
  KEY `quantity_unit_id` (`quantity_unit_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_key` VARCHAR(64) NOT NULL DEFAULT '',
  `type` ENUM('global','local') NOT NULL DEFAULT 'local',
  `key` VARCHAR(64) NOT NULL DEFAULT '',
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_settings_groups` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(64) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_shopping_carts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` CHAR(13) NOT NULL DEFAULT '',
  `customer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `customer_email` VARCHAR(128) NOT NULL DEFAULT '',
  `customer_company` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_tax_id` VARCHAR(32) NOT NULL DEFAULT '',
  `customer_address1` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_address2` VARCHAR(64) NOT NULL DEFAULT '',
  `customer_city` VARCHAR(32) NOT NULL DEFAULT '',
  `customer_postcode` VARCHAR(16) NOT NULL DEFAULT '',
  `customer_country_code` CHAR(2) NOT NULL DEFAULT '',
  `customer_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `customer_phone` VARCHAR(24) NOT NULL DEFAULT '',
  `different_shipping_adddress` TINYINT(1) NOT NULL DEFAULT '0',
  `shipping_company` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_firstname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_lastname` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address1` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_address2` VARCHAR(64) NOT NULL DEFAULT '',
  `shipping_city` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_postcode` VARCHAR(16) NOT NULL DEFAULT '',
  `shipping_country_code` CHAR(2) NOT NULL DEFAULT '',
  `shipping_zone_code` VARCHAR(8) NOT NULL DEFAULT '',
  `shipping_phone` VARCHAR(24) NOT NULL DEFAULT '',
  `shipping_option_id` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_option_name` VARCHAR(32) NOT NULL DEFAULT '',
  `shipping_option_userdata` VARCHAR(512) NOT NULL DEFAULT '',
  `payment_option_id` VARCHAR(32) NOT NULL DEFAULT '',
  `payment_option_name` VARCHAR(32) NOT NULL DEFAULT '',
  `payment_option_userdata` VARCHAR(512) NOT NULL DEFAULT '',
  `payment_terms` VARCHAR(8) NOT NULL DEFAULT '',
  `incoterm` VARCHAR(3) NOT NULL DEFAULT '',
  `weight_total` FLOAT(11,4) NOT NULL DEFAULT '0',
  `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `currency_code` CHAR(3) NOT NULL DEFAULT '',
  `subtotal` FLOAT(11,4) NOT NULL DEFAULT '0',
  `subtotal_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `lock_prices` TINYINT(1) NOT NULL DEFAULT '0',
  `display_prices_including_tax` TINYINT(1) NOT NULL DEFAULT '0',
  `client_ip` VARCHAR(39) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `public_key` VARCHAR(32) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_shopping_carts_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cart_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `stock_item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `key` VARCHAR(12) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `userdata` VARCHAR(1024) NOT NULL DEFAULT '',
  `image` VARCHAR(128) NOT NULL DEFAULT '',
  `quantity` FLOAT(11,4) NOT NULL DEFAULT '0',
  `quantity_unit_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `sku` VARCHAR(32) NOT NULL DEFAULT '',
  `gtin` VARCHAR(32) NOT NULL DEFAULT '',
  `taric` VARCHAR(32) NOT NULL DEFAULT '',
  `price` FLOAT(11,4) NOT NULL DEFAULT '0',
  `final_price` FLOAT(11,4) NOT NULL DEFAULT '0',
  `tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `tax_class_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `discount` FLOAT(11,4) NOT NULL DEFAULT '0',
  `discount_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `sum` FLOAT(11,4) NOT NULL DEFAULT '0',
  `sum_tax` FLOAT(11,4) NOT NULL DEFAULT '0',
  `weight` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
  `length` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `width` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `height` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
  `priority` INT NOT NULL DEFAULT '0',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `cart_id` (`cart_id`),
  INDEX `product_id` (`product_id`),
  INDEX `stock_item_id` (`stock_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_slides` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `languages` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `date_valid_from` TIMESTAMP NULL DEFAULT NULL,
  `date_valid_to` TIMESTAMP NULL DEFAULT NULL,
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_slides_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slide_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `caption` TEXT NOT NULL DEFAULT '',
  `link` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slide_info` (`slide_id`,`language_code`),
  KEY `slide_id` (`slide_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_sold_out_statuses_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sold_out_status_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sold_out_status_info` (`sold_out_status_id`, `language_code`),
  KEY `sold_out_status_id` (`sold_out_status_id`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_stock_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `quantity` FLOAT(11,4) NOT NULL DEFAULT '0',
  `quantity_unit_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `backordered` FLOAT(11,4) NOT NULL DEFAULT '0',
  `weight` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `weight_unit` VARCHAR(2) NOT NULL DEFAULT '',
  `length` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `width` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `height` FLOAT(11,4) UNSIGNED NOT NULL DEFAULT '0',
  `length_unit` VARCHAR(2) NOT NULL DEFAULT '',
  `purchase_price` FLOAT(11,4) NOT NULL DEFAULT '0',
  `purchase_price_currency_code` CHAR(3) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `sku` (`sku`),
  INDEX `mpn` (`mpn`),
  INDEX `gtin` (`gtin`),
  INDEX `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_stock_items_info` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `stock_item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `language_code` CHAR(2) NOT NULL DEFAULT '',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `stock_item_id` (`stock_item_id`),
  FULLTEXT INDEX `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_stock_transactions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `description` MEDIUMTEXT NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_stock_transactions_contents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `stock_item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `warehouse_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `quantity_adjustment` FLOAT(11,4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_suppliers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_classes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(64) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_tax_rates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tax_class_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `geo_zone_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `code` VARCHAR(32) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `description` VARCHAR(128) NOT NULL DEFAULT '',
  `rate` FLOAT(4,2) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_translations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lc_users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `username` VARCHAR(32) NOT NULL DEFAULT '',
  `email` VARCHAR(128) NOT NULL DEFAULT '',
  `password_hash` VARCHAR(255) NOT NULL DEFAULT '',
  `apps` VARCHAR(4096) NOT NULL DEFAULT '',
  `widgets` VARCHAR(512) NOT NULL DEFAULT '',
  `login_attempts` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `total_logins` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `last_ip_address` VARCHAR(39) NOT NULL DEFAULT '',
  `last_hostname` VARCHAR(128) NOT NULL DEFAULT '',
  `last_user_agent` VARCHAR(255) NOT NULL DEFAULT '',
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_code` CHAR(2) NOT NULL DEFAULT '',
  `code` VARCHAR(8) NOT NULL DEFAULT '',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
-- --------------------------------------------------------
CREATE TABLE `lc_zones_to_geo_zones` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `geo_zone_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `country_code` CHAR(2) NOT NULL DEFAULT '',
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
) ENGINE=InnoDB DEFAULT CHARSET={DB_DATABASE_CHARSET} COLLATE {DB_DATABASE_COLLATION};
