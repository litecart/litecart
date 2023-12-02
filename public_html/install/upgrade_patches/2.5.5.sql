ALTER TABLE `lc_currencies`
CHANGE COLUMN `status` `status` TINYINT(1) NOT NULL DEFAULT 0;
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items`
ADD COLUMN `priority` INT(11) NOT NULL DEFAULT 0 AFTER `dim_class`;
-- --------------------------------------------------------
ALTER TABLE `lc_newsletter_recipients` ADD `hostname` VARCHAR(128) NOT NULL DEFAULT '' AFTER `client_ip`, ADD `user_agent` VARCHAR(256) NOT NULL DEFAULT '' AFTER `hostname`;
-- --------------------------------------------------------
UPDATE `lc_settings_groups`
SET `description` = 'Settings related to customer accounts'
WHERE `key` = 'customer_details'
LIMIT 1;
