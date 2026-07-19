UPDATE `lc_settings` SET `value` = 'XAF' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'XAF' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0016500000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0015300000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('CM_VAT', 'CM VAT Zone', 'Cameroon', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'CM', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('CM_VAT', 'Cameroon VAT', 'Standard 19.25% VAT rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Cameroon VAT', '19.25%', '19.25000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);