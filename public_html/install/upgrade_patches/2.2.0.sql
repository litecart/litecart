CREATE TABLE `lc_categories_images` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`category_id` INT(11) NOT NULL,
	`filename` VARCHAR(256) NOT NULL,
	`checksum` CHAR(32) NOT NULL,
	`priority` TINYINT(2) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `category_id` (`category_id`)
) ENGINE=MyISAM;
-- --------------------------------------------------------
INSERT INTO `lc_categories_images` (category_id, filename) (
  SELECT id, image from `lc_categories`
  WHERE image != ''
);
-- --------------------------------------------------------
ALTER TABLE `lc_customers`
ADD COLUMN `num_logins` INT NOT NULL AFTER `password_reset_token`
ADD COLUMN `last_ip` VARCHAR(39) NOT NULL AFTER `num_login`,
ADD COLUMN `last_host` VARCHAR(64) NOT NULL AFTER `last_ip`,
ADD COLUMN `last_agent` VARCHAR(256) NOT NULL AFTER `last_host`,
ADD COLUMN `date_login` DATETIME NOT NULL AFTER `last_agent`;
-- --------------------------------------------------------
ALTER TABLE `lc_orders`
ADD COLUMN `shipping_tracking_url` VARCHAR(256) NOT NULL AFTER `shipping_tracking_id`,
ADD COLUMN `user_agent` VARCHAR(256) NOT NULL AFTER `client_ip`,
ADD COLUMN `domain` VARCHAR(64) NOT NULL AFTER `user_agent`;
-- --------------------------------------------------------
ALTER TABLE `lc_modules`
ADD COLUMN `date_pushed` DATETIME NOT NULL AFTER `last_log`;
-- --------------------------------------------------------
DELETE FROM `lc_settings`
WHERE `key` = 'job_error_reporter:last_run';
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('email', 'Send Emails', 'Wheither or not the platform should deliver outgoing emails.', 'email_status', '1', 'toggle("y/n")', '1', NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_sold_out_statuses` ADD COLUMN `hidden` TINYINT(1) NOT NULL AFTER `id`;
-- --------------------------------------------------------
ALTER TABLE `lc_sold_out_statuses` ADD INDEX `hidden` (`hidden`), ADD INDEX `orderable` (`orderable`);
-- --------------------------------------------------------
ALTER TABLE `lc_pages`
ADD COLUMN `parent_id` INT(11) NOT NULL AFTER `status`,
ADD INDEX `parent_id` (`parent_id`);
-- --------------------------------------------------------
ALTER TABLE `lc_users`
ADD COLUMN `email` VARCHAR(128) NOT NULL AFTER `username`,
CHANGE COLUMN `date_blocked` `date_valid_from` DATETIME NOT NULL AFTER `total_logins`,
CHANGE COLUMN `date_expires` `date_valid_to` DATETIME NOT NULL AFTER `date_valid_from`,
CHANGE COLUMN `last_ip` `last_ip` VARCHAR(39) NOT NULL AFTER `permissions`,
CHANGE COLUMN `last_host` `last_host` VARCHAR(128) NOT NULL AFTER `last_ip`,
ADD INDEX `status` (`status`),
ADD INDEX `username` (`username`),
ADD INDEX `email` (`email`);
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items`
CHANGE COLUMN `sku` `sku` VARCHAR(32) NOT NULL AFTER `name`,
ADD COLUMN `gtin` VARCHAR(32) NOT NULL AFTER `sku`,
ADD COLUMN `taric` VARCHAR(32) NOT NULL AFTER `gtin`,
ADD COLUMN `dim_x` DECIMAL(11,4) NOT NULL AFTER `weight_class`,
ADD COLUMN `dim_y` DECIMAL(11,4) NOT NULL AFTER `dim_x`,
ADD COLUMN `dim_z` DECIMAL(11,4) NOT NULL AFTER `dim_y`,
ADD COLUMN `dim_class` VARCHAR(2) NOT NULL AFTER `dim_z`;
-- --------------------------------------------------------
ALTER TABLE `lc_order_statuses` ADD COLUMN `keywords` VARCHAR(256) NOT NULL AFTER `color`;
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `value` = 1 / `value`;
-- --------------------------------------------------------
UPDATE `lc_orders` SET currency_value = 1 / currency_value;
-- --------------------------------------------------------
DELETE FROM `lc_translations` WHERE code = 'terms_cookies_acceptance';
