ALTER TABLE `lc_customers`
ADD COLUMN `last_ip` VARCHAR(39) NOT NULL AFTER `password_reset_token`,
ADD COLUMN `last_agent` VARCHAR(256) NOT NULL AFTER `last_ip`,
ADD COLUMN `date_login` DATETIME NOT NULL AFTER `last_ip`;
-- --------------------------------------------------------
ALTER TABLE `lc_orders`
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
)