UPDATE `lc_settings` SET `value` = 'USD' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'USD' WHERE `key` = 'store_currency_code';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('EC_VAT', 'EC VAT Zone', 'Ecuador', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'EC', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('EC_IVA', 'Ecuador IVA', 'Standard 15% IVA rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('EC_IVA_REDUCED', 'Ecuador IVA (Reduced)', 'Reduced 0% IVA rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID_2 = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Ecuador IVA', '15%', '15.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@TAX_CLASS_ID_2, @VAT_ZONE_ID, 'percent', 'Ecuador IVA (Reduced)', '0%', '0.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);