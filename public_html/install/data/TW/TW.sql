UPDATE `lc_settings` SET `value` = 'TWD' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'TWD' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0320000000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0297000000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('TW_VAT', 'TW VAT Zone', 'Taiwan', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'TW', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('TW_VAT', 'Taiwan VAT', 'Business Tax 5%', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Taiwan VAT', '5%', '5.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);