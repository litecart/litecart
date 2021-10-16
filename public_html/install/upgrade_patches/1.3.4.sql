ALTER TABLE `lc_orders_items` CHANGE `quantity` `quantity` DECIMAL(11, 4) NOT NULL;
-- --------------------------------------------------------
DELETE FROM `lc_settings` WHERE `key` = 'contact_form_captcha_enabled';
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('security', 'local', 'CAPTCHA', 'Prevent robots from posting form data by enabling CAPTCHA security.', 'captcha_enabled', '1', 'toggle()', 15, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_customers` ADD `status` TINYINT(1) NOT NULL DEFAULT 1 AFTER `id`;
-- --------------------------------------------------------
ALTER TABLE `lc_translations` CHANGE COLUMN `code` `code` VARCHAR(250) NOT NULL;
-- --------------------------------------------------------
UPDATE `lc_categories` SET dock = CONCAT_WS(',', if(dock = '', null, dock), 'tree') WHERE parent_id = 0;
-- --------------------------------------------------------
ALTER TABLE `lc_products` ADD purchase_price_currency_code VARCHAR(3) NOT NULL AFTER purchase_price;
-- --------------------------------------------------------
ALTER TABLE `lc_orders_comments` ADD COLUMN `author` ENUM('system','staff','customer') NOT NULL AFTER `order_id`, CHANGE COLUMN `hidden` `hidden` INT(11) NOT NULL AFTER `text`;
-- --------------------------------------------------------
ALTER TABLE `lc_products_images` ADD COLUMN `checksum` CHAR(32) NOT NULL AFTER `filename`;
-- --------------------------------------------------------
ALTER TABLE `lc_categories`	ADD COLUMN `google_taxonomy_id` INT(11) NOT NULL AFTER `parent_id`;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `name` = 'Serbia', `domestic_name` = '', `iso_code_1` = '381', `iso_code_2` = 'RS', `iso_code_3` = 'SRB', `tax_id_format` = '', `address_format` = '%company\r\n%firstname %lastname\r\n%address1\r\n%address2\r\n%postcode %city\r\n%zone_name\r\n%country_name', `postcode_format` = '', `postcode_required` = 0, `language_code` = 'sr', `currency_code` = 'RSD', `phone_code` = '381' WHERE iso_code_2 = 'YU';
