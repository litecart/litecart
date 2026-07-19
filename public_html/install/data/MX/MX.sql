ALTER TABLE `lc_translations` ADD `text_es` text NOT NULL AFTER `text_en`;
-- -----
UPDATE `lc_settings` SET `value` = 'MXN' WHERE `key` in ('store_currency_code', 'default_currency_code');
-- -----
UPDATE `lc_currencies` SET `value` = 0.0574 WHERE `code` = 'USD' LIMIT 1;
-- -----
UPDATE `lc_currencies` SET `value` = 0.0673 WHERE `code` = 'EUR' LIMIT 1;
-- -----
INSERT INTO `lc_geo_zones` (`name`, `description`, `updated_at`, `created_at`) VALUES
('MX Tax Zone', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @MX_TAX_ZONE = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `updated_at`, `created_at`) VALUES
(@MX_TAX_ZONE, 'MX', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `name`, `description`, `rate`, `rule_com),
(ies_with_tax_id`, `rule_companies_without_tax_id`, `rule_individuals_with_tax_id`, `rule_individuals_without_tax_id`, `updated_at`, `created_at`) VALUES
(1, @MX_TAX_ZONE, 'MX IVA 16%', '', 16.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, @MX_TAX_ZONE, 'MX IVA 8%', 'Border zone rate', 8.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);