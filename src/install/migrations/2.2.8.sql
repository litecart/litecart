ALTER TABLE `lc_categories` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_currencies` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_languages`
CHANGE COLUMN `priority` `priority` INT(11) NOT NULL DEFAULT '0',
CHANGE COLUMN `locale` `locale` VARCHAR(64) NOT NULL DEFAULT '',
ADD COLUMN `url_type` VARCHAR(16) NOT NULL DEFAULT 'path' AFTER `charset`,
ADD COLUMN `domain_name` VARCHAR(64) NOT NULL DEFAULT '' AFTER `url_type`;
-- -----
ALTER TABLE `lc_modules` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_orders_comments`
CHANGE COLUMN `author` `author` ENUM('system', 'staff', 'customer') NOT NULL DEFAULT 'system';
-- -----
ALTER TABLE `lc_order_statuses` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_orders_totals` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_pages` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_products_images` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_products_options` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_products_options_values` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_products_options_stock` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_quantity_units` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
DELETE FROM `lc_settings` WHERE `key` = 'seo_links_language_prefix';
-- -----
ALTER TABLE `lc_settings` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_settings_groups` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_slides` CHANGE `priority` `priority` INT(11) NOT NULL DEFAULT '0';
-- -----
ALTER TABLE `lc_translations`
ADD COLUMN `date_accessed` TIMESTAMP NULL AFTER `backend`;
-- -----
ALTER TABLE `lc_users`
CHANGE COLUMN `permissions` `apps` VARCHAR(4096) NOT NULL DEFAULT '',
ADD `widgets` VARCHAR(512) NOT NULL DEFAULT '' AFTER `apps`;
