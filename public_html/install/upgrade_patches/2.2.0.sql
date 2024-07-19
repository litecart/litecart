DROP TABLE IF EXISTS `lc_addresses`;
-- -----
CREATE TABLE IF NOT EXISTS `lc_attribute_groups` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`code` varchar(32) NOT NULL,
	`sort` ENUM('alphabetical','priority') NOT NULL DEFAULT 'alphabetical',
	`date_updated` datetime NOT NULL,
	`date_created` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `code` (`code`)
) ENGINE=MyISAM;
-- -----
CREATE TABLE IF NOT EXISTS `lc_attribute_groups_info` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`group_id` int(11) NOT NULL,
	`language_code` varchar(2) NOT NULL,
	`name` varchar(128) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `attribute_group` (`group_id`,`language_code`),
	KEY `group_id` (`group_id`),
	KEY `language_code` (`language_code`)
) ENGINE=MyISAM;
-- -----
CREATE TABLE IF NOT EXISTS `lc_attribute_values` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`group_id` int(11) NOT NULL,
	`date_updated` datetime NOT NULL,
	`date_created` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `group_id` (`group_id`)
) ENGINE=MyISAM;
-- -----
CREATE TABLE IF NOT EXISTS `lc_attribute_values_info` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`value_id` int(11) NOT NULL,
	`language_code` varchar(2) NOT NULL,
	`name` varchar(128) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `attribute_value` (`value_id`,`language_code`),
	KEY `value_id` (`value_id`),
	KEY `language_code` (`language_code`)
) ENGINE=MyISAM;
-- -----
INSERT INTO `lc_attribute_groups`
(id, date_updated, date_created)
SELECT id, date_updated, date_created FROM `lc_product_groups`;
-- -----
INSERT INTO `lc_attribute_groups_info`
(id, group_id, language_code, name)
SELECT id, product_group_id, language_code, name FROM `lc_product_groups_info`;
-- -----
INSERT INTO `lc_attribute_values`
(id, group_id, date_updated, date_created)
SELECT id, product_group_id, date_updated, date_created FROM `lc_product_groups_values`;
-- -----
INSERT INTO `lc_attribute_values_info`
(id, value_id, language_code, name)
SELECT id, product_group_value_id, language_code, name FROM `lc_product_groups_values_info`;
-- -----
CREATE TABLE `lc_categories_filters` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`category_id` INT(11) NOT NULL,
	`select_multiple` TINYINT(1) NOT NULL,
	`attribute_group_id` INT(11) NOT NULL,
	`priority` INT(11) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `attribute_filter` (`category_id`, `attribute_group_id`),
	KEY `category_id` (`category_id`)
) ENGINE=MyISAM;
-- -----
CREATE TABLE `lc_categories_images` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`category_id` INT(11) NOT NULL,
	`filename` VARCHAR(256) NOT NULL,
	`checksum` CHAR(32) NOT NULL,
	`priority` TINYINT(2) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `category_id` (`category_id`)
) ENGINE=MyISAM;
-- -----
INSERT INTO `lc_categories_images` (category_id, filename) (
	SELECT id, image FROM `lc_categories`
	WHERE image != ''
);
-- -----
ALTER TABLE `lc_customers`
CHANGE COLUMN `password` `password_hash` VARCHAR(256) NOT NULL,
ADD COLUMN `total_logins` INT(11) NOT NULL AFTER `password_reset_token`,
ADD COLUMN `last_ip` VARCHAR(39) NOT NULL AFTER `total_logins`,
ADD COLUMN `last_host` VARCHAR(128) NOT NULL AFTER `last_ip`,
ADD COLUMN `last_agent` VARCHAR(256) NOT NULL AFTER `last_host`,
ADD COLUMN `date_login` DATETIME NOT NULL AFTER `last_agent`;
-- -----
CREATE TABLE `lc_emails` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`status` ENUM('draft','scheduled','sent','error') NOT NULL DEFAULT 'draft',
	`code` VARCHAR(256) NOT NULL,
	`charset` VARCHAR(16) NOT NULL,
	`sender` VARCHAR(256) NOT NULL,
	`recipients` TEXT NOT NULL,
	`ccs` TEXT NOT NULL,
	`bccs` TEXT NOT NULL,
	`subject` VARCHAR(256) NOT NULL,
	`multiparts` MEDIUMTEXT NOT NULL,
	`date_scheduled` DATETIME NOT NULL,
	`date_sent` DATETIME NOT NULL,
	`date_updated` DATETIME NOT NULL,
	`date_created` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	KEY `date_scheduled` (`date_scheduled`),
	KEY `code` (`code`),
	KEY `date_created` (`date_created`),
	KEY `sender_email` (`sender`)
) ENGINE=MyISAM;
-- -----
ALTER TABLE `lc_orders`
ADD COLUMN `shipping_tracking_url` VARCHAR(256) NOT NULL AFTER `shipping_tracking_id`,
ADD COLUMN `user_agent` VARCHAR(256) NOT NULL AFTER `client_ip`,
ADD COLUMN `domain` VARCHAR(64) NOT NULL AFTER `user_agent`;
-- -----
ALTER TABLE `lc_modules`
ADD COLUMN `date_pushed` DATETIME NOT NULL AFTER `last_log`,
ADD COLUMN `date_processed` DATETIME NOT NULL AFTER `date_pushed`,
CHANGE COLUMN `date_updated` `date_updated` DATETIME NOT NULL,
CHANGE COLUMN `date_created` `date_created` DATETIME NOT NULL;
-- -----
ALTER TABLE `lc_settings` CHANGE COLUMN `value` `value` VARCHAR(8192) NOT NULL;
-- -----
DELETE FROM `lc_settings`
WHERE `key` = 'job_error_reporter:last_run';
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('email', 'Send Emails', 'Whether or not the platform should deliver outgoing emails.', 'email_status', '1', 'toggle("y/n")', '1', NOW(), NOW());
-- -----
INSERT IGNORE INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES ('legal', 'Legal', 'Legal settings and information', 50);
-- -----
INSERT IGNORE INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('legal', 'local', 'Cookie Policy', 'Select a page for the cookie policy or leave blank to disable.', 'cookie_policy', '', 'page()', 10, NOW(), NOW()),
('legal', 'local', 'Privacy Policy', 'Select a page for the privacy policy consent or leave blank to disable.', 'privacy_policy', '', 'page()', 11, NOW(), NOW()),
('legal', 'local', 'Terms of Purchase', 'Select a page for the terms of purchase or leave blank to disable.', 'terms_of_purchase', '', 'page()', 12, NOW(), NOW());
-- -----
ALTER TABLE `lc_sold_out_statuses`
ADD COLUMN `hidden` TINYINT(1) NOT NULL AFTER `id`;
-- -----
ALTER TABLE `lc_sold_out_statuses`
ADD KEY `hidden` (`hidden`),
ADD KEY `orderable` (`orderable`);
-- -----
ALTER TABLE `lc_pages`
ADD COLUMN `parent_id` INT(11) NOT NULL AFTER `status`,
ADD KEY `parent_id` (`parent_id`);
-- -----
ALTER TABLE `lc_products` CHANGE COLUMN `quantity_unit_id` `quantity_unit_id` INT(11) NOT NULL;
-- -----
DROP TABLE `lc_product_groups`;
-- -----
DROP TABLE `lc_product_groups_info`;
-- -----
DROP TABLE `lc_product_groups_values`;
-- -----
DROP TABLE `lc_product_groups_values_info`;
-- -----
CREATE TABLE `lc_products_attributes` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`product_id` INT(11) NOT NULL,
	`group_id` INT(11) NOT NULL,
	`value_id` INT(11) NOT NULL,
	`custom_value` VARCHAR(256) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `id` (`id`, `product_id`, `group_id`, `value_id`),
	KEY `product_id` (`product_id`),
	KEY `group_id` (`group_id`),
	KEY `value_id` (`value_id`)
) ENGINE=MyISAM;
-- -----
ALTER TABLE `lc_products_info`
CHANGE COLUMN `attributes` `technical_data` TEXT NOT NULL AFTER `description`,
ADD FULLTEXT KEY `name` (`name`),
ADD FULLTEXT KEY `short_description` (`short_description`),
ADD FULLTEXT KEY `description` (`description`);
-- -----
ALTER TABLE `lc_users`
ADD COLUMN `email` VARCHAR(128) NOT NULL AFTER `username`,
CHANGE COLUMN `date_blocked` `date_valid_from` DATETIME NOT NULL AFTER `total_logins`,
CHANGE COLUMN `date_expires` `date_valid_to` DATETIME NOT NULL AFTER `date_valid_from`,
CHANGE COLUMN `last_ip` `last_ip` VARCHAR(39) NOT NULL AFTER `permissions`,
CHANGE COLUMN `last_host` `last_host` VARCHAR(128) NOT NULL AFTER `last_ip`,
ADD KEY `status` (`status`),
ADD KEY `username` (`username`),
ADD KEY `email` (`email`);
-- -----
ALTER TABLE `lc_orders`
ADD COLUMN `starred` TINYINT(1) NOT NULL AFTER `uid`,
ADD COLUMN `unread` TINYINT(1) NOT NULL AFTER `starred`,
ADD COLUMN `reference` VARCHAR(128) NOT NULL AFTER `payment_transaction_id`,
ADD COLUMN `display_prices_including_tax` TINYINT(1) NOT NULL AFTER `currency_value`,
ADD KEY `starred` (`starred`),
ADD KEY `unread` (`unread`);
-- -----
ALTER TABLE `lc_orders_items`
CHANGE COLUMN `sku` `sku` VARCHAR(32) NOT NULL AFTER `name`,
ADD COLUMN `gtin` VARCHAR(32) NOT NULL AFTER `sku`,
ADD COLUMN `taric` VARCHAR(32) NOT NULL AFTER `gtin`,
ADD COLUMN `dim_x` DECIMAL(11,4) NOT NULL AFTER `weight_class`,
ADD COLUMN `dim_y` DECIMAL(11,4) NOT NULL AFTER `dim_x`,
ADD COLUMN `dim_z` DECIMAL(11,4) NOT NULL AFTER `dim_y`,
ADD COLUMN `dim_class` VARCHAR(2) NOT NULL AFTER `dim_z`;
-- -----
ALTER TABLE `lc_order_statuses` ADD COLUMN `keywords` VARCHAR(256) NOT NULL AFTER `color`;
-- -----
UPDATE `lc_currencies` SET `value` = 1 / `value`;
-- -----
UPDATE `lc_orders` SET currency_value = 1 / currency_value;
-- -----
DELETE FROM `lc_translations` WHERE code = 'terms_cookies_acceptance';
-- -----
ALTER TABLE `lc_tax_rates`
CHANGE COLUMN `address_type` `address_type` ENUM('payment','shipping') NOT NULL DEFAULT 'shipping' AFTER `type`,
ADD COLUMN `rule_companies_with_tax_id` TINYINT(1) NOT NULL AFTER `tax_id_rule`,
ADD COLUMN `rule_companies_without_tax_id` TINYINT(1) NOT NULL AFTER `rule_companies_with_tax_id`,
ADD COLUMN `rule_individuals_with_tax_id` TINYINT(1) NOT NULL AFTER `rule_companies_without_tax_id`,
ADD COLUMN `rule_individuals_without_tax_id` TINYINT(1) NOT NULL AFTER `rule_individuals_with_tax_id`
-- -----
UPDATE `lc_tax_rates` SET rule_companies_with_tax_id = 1 WHERE customer_type IN ('both', 'companies') AND tax_id_rule IN ('both', 'with');
-- -----
UPDATE `lc_tax_rates` SET rule_companies_without_tax_id = 1 WHERE customer_type IN ('both', 'companies') AND tax_id_rule IN ('both', 'without');
-- -----
UPDATE `lc_tax_rates` SET rule_individuals_with_tax_id = 1 WHERE customer_type IN ('both', 'individuals') AND tax_id_rule IN ('both', 'with');
-- -----
UPDATE `lc_tax_rates` SET rule_individuals_without_tax_id = 1 WHERE customer_type IN ('both', 'individuals') AND tax_id_rule IN ('both', 'without');
-- -----
ALTER TABLE `lc_tax_rates`
DROP COLUMN `customer_type`,
DROP COLUMN `tax_id_rule`;
-- -----
ALTER TABLE `lc_translations` CHANGE COLUMN `frontend` `frontend` TINYINT(1) NOT NULL AFTER `html`;
-- -----
ALTER TABLE `lc_categories`
DROP COLUMN `dock`;
-- -----
DELETE FROM `lc_settings` WHERE `key` = 'seo_links_enabled';
-- -----
UPDATE `lc_settings` SET `function` = 'text()' WHERE `function` IN ('input()', 'smallinput()', 'smalltext()');
-- -----
UPDATE `lc_settings` SET `function` = 'country()' WHERE `function` = 'countries()';
-- -----
UPDATE `lc_settings` SET `function` = 'currency()' WHERE `function` = 'currencies()';
-- -----
UPDATE `lc_settings` SET `function` = 'timezone()' WHERE `function` = 'timezones()';
-- -----
UPDATE `lc_settings` SET `function` = 'language()' WHERE `function` = 'languages()';
-- -----
UPDATE `lc_settings` SET `function` = 'zone()' WHERE `function` = 'zones()';
-- -----
UPDATE `lc_settings` SET `function` = 'weight_class()' WHERE `function` = 'weight_classes()';
-- -----
UPDATE `lc_settings` SET `function` = 'length_class()' WHERE `function` = 'length_classes()';
-- -----
UPDATE `lc_settings` SET `function` = 'tax_class()' WHERE `function` = 'tax_classes()';
-- -----
UPDATE `lc_settings` SET `function` = 'quantity_unit()' WHERE `function` = 'quantity_units()';
-- -----
UPDATE `lc_settings` SET `function` = 'sold_out_status()' WHERE `function` = 'sold_out_statuses()';
-- -----
UPDATE `lc_settings` SET `function` = 'delivery_status()' WHERE `function` = 'delivery_statuses()';
-- -----
UPDATE `lc_settings` SET `function` = 'number()' WHERE `function` = 'int()';
-- -----
UPDATE `lc_settings` SET `function` = 'template("admin")' WHERE `function` = 'templates("admin")';
-- -----
UPDATE `lc_settings` SET `function` = 'template("catalog")' WHERE `function` = 'templates("catalog")';
-- -----
ALTER TABLE `lc_users`
CHANGE COLUMN `password` `password_hash` VARCHAR(256) NOT NULL,
CHANGE COLUMN `date_created` `date_created` DATETIME NOT NULL AFTER `date_updated`;
-- -----
DELETE FROM `lc_settings` WHERE `key` IN ('security_blacklist', 'security_session_hijacking', 'security_http_post', 'security_bot_trap', 'security_xss', 'security_bad_urls');
