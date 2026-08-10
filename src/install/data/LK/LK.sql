UPDATE `lc_settings` SET `value` = 'LKR' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'LKR' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0031000000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0029000000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`)
VALUES('LK_VAT', 'LK VAT Zone', 'Sri Lanka', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`)
VALUES(@VAT_ZONE_ID, 'LK', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`)
VALUES('LK_VAT', 'Sri Lanka VAT', 'Standard 15% VAT rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`)
VALUES(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Sri Lanka VAT', '15%', '15.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);