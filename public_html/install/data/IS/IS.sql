UPDATE `lc_settings` SET `value` = 'ISK' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'ISK' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0072000000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0067000000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('IS_VAT', 'IS VAT Zone', 'Iceland', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'IS', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('IS_STANDARD', 'Iceland Standard VAT', 'Standard 24% VAT rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('IS_REDUCED', 'Iceland Reduced VAT', 'Reduced 11% VAT rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID_2 = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Iceland VAT', '24%', '24.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@TAX_CLASS_ID_2, @VAT_ZONE_ID, 'percent', 'Iceland VAT (Reduced)', '11%', '11.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);