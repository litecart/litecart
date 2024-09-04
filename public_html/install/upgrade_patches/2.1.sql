UPDATE `lc_settings` SET `value` = 4 WHERE `key` = 'box_recently_viewed_products_num_items' LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES
('email', 'Email', 'Email and SMTP', 30);
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('email', 'local', 'SMTP Enabled', 'Whether or not to use an SMTP server for delivering email.', 'smtp_status', '0', 'toggle("e/d")', 10, NOW(), NOW()),
('email', 'local', 'SMTP Host', 'SMTP hostname e.g. smtp.myprovider.com.', 'smtp_host', 'localhost', 'input()', 11, NOW(), NOW()),
('email', 'local', 'SMTP Port', 'SMTP port e.g. 25, 465 (SSL/TLS), or 587 (STARTTLS).', 'smtp_port', '25', 'number()', 12, NOW(), NOW()),
('email', 'local', 'SMTP Username', 'Username for SMTP authentication.', 'smtp_username', '', 'input()', 13, NOW(), NOW()),
('email', 'local', 'SMTP Password', 'Password for SMTP authentication.', 'smtp_password', '', 'input()', 14, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_customers`
CHANGE COLUMN `postcode` `postcode` VARCHAR(16) NOT NULL,
CHANGE COLUMN `shipping_postcode` `shipping_postcode` VARCHAR(16) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_orders`
CHANGE COLUMN `customer_postcode` `customer_postcode` VARCHAR(16) NOT NULL,
CHANGE COLUMN `shipping_postcode` `shipping_postcode` VARCHAR(16) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products`
CHANGE COLUMN `code` `code` VARCHAR(32) NOT NULL,
CHANGE COLUMN `sku` `sku` VARCHAR(32) NOT NULL,
ADD COLUMN `mpn` VARCHAR(32) NOT NULL AFTER `sku`,
ADD INDEX `sku` (`sku`),
ADD INDEX `mpn` (`mpn`),
ADD INDEX `gtin` (`gtin`),
ADD INDEX `taric` (`taric`);
-- --------------------------------------------------------
ALTER TABLE `lc_translations`
CHANGE COLUMN `date_accessed` `date_accessed` DATETIME NOT NULL AFTER `pages`;
-- --------------------------------------------------------
ALTER TABLE `lc_settings`
CHANGE COLUMN `key` `key` VARCHAR(64) NOT NULL AFTER `type`;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `postcode_format` = '[1-9][0-9]{3} ?[a-zA-Z]{2}' WHERE `iso_code_2` = 'NL';
-- --------------------------------------------------------
ALTER TABLE `lc_manufacturers`
CHANGE COLUMN `status` `status` TINYINT(1) NOT NULL,
ADD COLUMN `featured` TINYINT(1) NOT NULL AFTER `status`;
-- --------------------------------------------------------
ALTER TABLE `lc_product_groups` CHANGE COLUMN `status` `status` TINYINT(1) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_slides` CHANGE COLUMN `image` `image` VARCHAR(256) NOT NULL;
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lc_addresses`;
-- --------------------------------------------------------
ALTER TABLE `lc_currencies` CHANGE COLUMN `value` `value` DECIMAL(11,6) NOT NULL AFTER `name`;
-- --------------------------------------------------------
ALTER TABLE `lc_orders` CHANGE COLUMN `currency_value` `currency_value` DECIMAL(11,6) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_orders_totals` CHANGE COLUMN `value` `value` DECIMAL(11,4) NOT NULL, CHANGE COLUMN `tax` `tax` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_manufacturers` ADD INDEX(`featured`);