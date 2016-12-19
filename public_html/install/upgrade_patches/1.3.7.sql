ALTER TABLE `lc_tax_rates` ADD COLUMN `address_type` ENUM('payment','shipping') NOT NULL DEFAULT 'payment' AFTER `rate`;
-- --------------------------------------------------------
ALTER TABLE `lc_settings` CHANGE `value` `value` VARCHAR(8192);
-- --------------------------------------------------------
UPDATE `lc_settings` SET `type` = 'global' WHERE `key` = 'platform_database_version' LIMIT 1;
-- --------------------------------------------------------
ALTER TABLE `lc_orders_comments` CHANGE COLUMN `hidden` `hidden` TINYINT(1) NOT NULL;