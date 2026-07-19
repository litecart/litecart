ALTER TABLE `lc_translations` ADD `text_ar` text NOT NULL AFTER `text_en`;
-- -----
ALTER TABLE `lc_translations` ADD `text_fr` text NOT NULL AFTER `text_en`;
-- -----
UPDATE `lc_settings` SET `value` = 'MAD' WHERE `key` in ('store_currency_code', 'default_currency_code');
-- -----
UPDATE `lc_currencies` SET `value` = 0.1015 WHERE `code` = 'USD' LIMIT 1;
-- -----
UPDATE `lc_currencies` SET `value` = 0.1190 WHERE `code` = 'EUR' LIMIT 1;
-- -----
INSERT INTO `lc_geo_zones` (`name`, `description`, `updated_at`, `created_at`) VALUES
('MA Tax Zone', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @MA_TAX_ZONE = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `updated_at`, `created_at`) VALUES
(@MA_TAX_ZONE, 'MA', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `name`, `description`, `rate`, `rule_companies_with_tax_id`, `rule_companies_without_tax_id`, `rule_individuals_with_tax_id`, `rule_individuals_without_tax_id`, `updated_at`, `created_at`) VALUES
(1, @MA_TAX_ZONE, 'MA TVA 20%', '', 20.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, @MA_TAX_ZONE, 'MA TVA 14%', '', 14.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, @MA_TAX_ZONE, 'MA TVA 10%', '', 10.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(4, @MA_TAX_ZONE, 'MA TVA 7%', '', 7.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);