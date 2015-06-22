INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('', 'global', 'Jobs Last Push', 'Time when background jobs where last pushed for execution.', 'jobs_last_push', now(), 'input()', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_cart_items` ADD COLUMN `cart_uid` VARCHAR(13) NOT NULL AFTER `customer_id`, ADD `key` VARCHAR(32) NOT NULL AFTER `cart_uid`, CHANGE `option_id` `options` VARCHAR(2048) NOT NULL, CHANGE `quantity` `quantity` DECIMAL(11, 4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_cart_items` ADD INDEX `cart_uid` (`cart_uid`);
-- --------------------------------------------------------
ALTER TABLE `lc_products` CHANGE `quantity` `quantity` DECIMAL(11, 4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_options_stock` CHANGE `quantity` `quantity` DECIMAL(11, 4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_info` DROP COLUMN `meta_keywords`;
-- --------------------------------------------------------
ALTER TABLE `lc_categories_info` DROP COLUMN `meta_keywords`;
-- --------------------------------------------------------
ALTER TABLE `lc_manufacturers_info` DROP COLUMN `meta_keywords`;
-- --------------------------------------------------------
ALTER TABLE `lc_pages_info` DROP COLUMN `meta_keywords`;
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-chain-broken' where icon = 'chain-broken';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-clock-o' where icon = 'clock-o';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-cog' where icon = 'cog';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-truck' where icon = 'truck';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-times' where icon = 'times';