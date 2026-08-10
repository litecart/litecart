UPDATE `lc_settings` SET `value` = 'MYR' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'MYR' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.2240000000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.2080000000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('MY_VAT', 'MY VAT Zone', 'Malaysia', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'MY', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('MY_SST', 'Malaysia SST', 'Sales and Service Tax', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Malaysia SST (Sales)', '10%', '10.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);