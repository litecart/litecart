INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `updated_at`) VALUES
('1', 'CDF', '976', 'Congolese Franc', '1.00000000', '2', '', ' FC', '1', CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_products` ADD `price_cdf` DECIMAL(11,4) NOT NULL AFTER `price_usd`;
-- -----
ALTER TABLE `lc_products` ADD `campaign_price_cdf` DECIMAL(11,4) NOT NULL AFTER `campaign_price_usd`;
-- -----
UPDATE `lc_settings` SET `value` = 'CDF' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'CDF' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00038000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00035000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('CD_VAT', 'CD VAT Zone', 'Democratic Republic of the Congo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'CD', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('CD_VAT', 'DR Congo VAT', 'Standard 16% VAT rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'DR Congo VAT', '16%', '16.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);