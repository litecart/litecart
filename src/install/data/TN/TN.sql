UPDATE `lc_settings` SET `value` = 'TND' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'TND' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.3180000000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.2950000000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('TN_VAT', 'TN VAT Zone', 'Tunisia', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'TN', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('TN_VAT', 'Tunisia VAT', 'Standard 19% VAT rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('TN_VAT_REDUCED', 'Tunisia VAT (Reduced)', 'Reduced 7% VAT rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID_2 = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Tunisia VAT', '19%', '19.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@TAX_CLASS_ID_2, @VAT_ZONE_ID, 'percent', 'Tunisia VAT (Reduced)', '7%', '7.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);