INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `charset`, `raw_date`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `currency_size`, `image`, `direction`, `updated_at`, `created_at`) VALUES
('1', 'sw', 'swa', 'Kiswahili', 'sw_KE.UTF-8', 'UTF-8', '%e %B %Y', '%e %b %Y', '%H:%M', '%e %b %Y %H:%M', '.', ',', 'KES', '2', '', 'ltr', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `updated_at`) VALUES
('1', 'KES', '404', 'Kenyan Shilling', '1.00000000', '2', '', ' KSh', '1', CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_products` ADD `price_kes` DECIMAL(11,4) NOT NULL AFTER `price_usd`;
-- -----
ALTER TABLE `lc_products` ADD `campaign_price_kes` DECIMAL(11,4) NOT NULL AFTER `campaign_price_usd`;
-- -----
UPDATE `lc_settings` SET `value` = 'KES' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'KES' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0077000000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0072000000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`)
VALUES('KE_VAT', 'KE VAT Zone', 'Kenya', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`)
VALUES(@VAT_ZONE_ID, 'KE', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`)
VALUES('KE_VAT', 'Kenya VAT', 'Standard 16% VAT rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`)
VALUES(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Kenya VAT', '16%', '16.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);