UPDATE `lc_settings` SET `value` = 'MMK' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'MMK' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00048000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00044000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('MM_VAT', 'MM VAT Zone', 'Myanmar', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'MM', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('MM_CIT', 'Myanmar Commercial Tax', 'Standard 5% Commercial Tax', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Myanmar Commercial Tax', '5%', '5.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);