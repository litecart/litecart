INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `charset`, `raw_date`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `currency_size`, `image`, `direction`, `date_created`, `date_updated`) VALUES
('1', 'sw', 'swa', 'Kiswahili', 'sw_TZ.UTF-8', 'UTF-8', '%e %B %Y', '%e %b %Y', '%H:%M', '%e %b %Y %H:%M', '.', ',', 'TZS', '2', '', 'ltr', NOW(), NOW());
-- -----
ALTER TABLE `lc_categories_info` ADD `text_sw` TEXT COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `text_en`;
-- -----
ALTER TABLE `lc_attribute_groups_info` ADD `text_sw` TEXT COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `text_en`;
-- -----
ALTER TABLE `lc_attributes_info` ADD `text_sw` TEXT COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `text_en`;
-- -----
ALTER TABLE `lc_products_info` ADD `text_sw` TEXT COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `text_en`;
-- -----
ALTER TABLE `lc_pages_info` ADD `text_sw` TEXT COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `text_en`;
-- -----
ALTER TABLE `lc_slides_info` ADD `text_sw` TEXT COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `text_en`;
-- -----
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`) VALUES
('1', 'TZS', '834', 'Tanzanian Shilling', '1.00000000', '2', '', ' TSh', '1', NOW());
-- -----
ALTER TABLE `lc_products` ADD `price_tzs` DECIMAL(11,4) NOT NULL AFTER `price_usd`;
-- -----
ALTER TABLE `lc_products` ADD `campaign_price_tzs` DECIMAL(11,4) NOT NULL AFTER `campaign_price_usd`;
-- -----
UPDATE `lc_settings` SET `value` = 'TZS' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'TZS' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00040000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00037000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `date_created`, `date_updated`)
VALUES('TZ_VAT', 'TZ VAT Zone', 'Tanzania', NOW(), NOW());
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_created`)
VALUES(@VAT_ZONE_ID, 'TZ', '', NOW());
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `date_created`, `date_updated`)
VALUES('TZ_VAT', 'Tanzania VAT', 'Standard 18% VAT rate', NOW(), NOW());
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `date_created`, `date_updated`)
VALUES(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Tanzania VAT', '18%', '18.00000', NOW(), NOW());