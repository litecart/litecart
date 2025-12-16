INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `locale_intl`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `priority`, `updated_at`, `created_at`) VALUES
(1, 'ro', 'ron', 'Română', 'ro_RO.utf8,ro_RO.UTF-8,romanian', 'ro_RO', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%b %e %Y', '%H:%M', '%b %e %Y %H:%M', ',', ' ', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_translations` ADD `text_ro` text NOT NULL AFTER `text_en`;
-- -----
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `updated_at`, `created_at`) VALUES
(1, 'RON', '946', 'Leu românesc', 1.00, 2, '', ' lei', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_products_prices` ADD `RON` DECIMAL(11,4) NOT NULL;
-- -----
UPDATE `lc_settings` SET `value` = 'RON' WHERE `key` in ('store_currency_code', 'default_currency_code');
-- -----
UPDATE `lc_currencies` SET `value` = 4.25 WHERE `code` = 'USD' LIMIT 1;
-- -----
UPDATE `lc_currencies` SET `value` = 4.98 WHERE `code` = 'EUR' LIMIT 1;
-- -----
INSERT INTO `lc_geo_zones` (`name`, `description`, `updated_at`, `created_at`) VALUES
('RO VAT Zone', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @RO_VAT_ZONE = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `updated_at`, `created_at`) VALUES
(@RO_VAT_ZONE, 'RO', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_geo_zones` (`name`, `description`, `updated_at`, `created_at`) VALUES
('EU VAT Zone', 'European Union excl. RO', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @EU_VAT_ZONE = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `updated_at`, `created_at`) VALUES
(@EU_VAT_ZONE, 'AT', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'BE', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'BG', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'CY', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'CZ', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'DE', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'DK', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'EE', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'ES', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'FI', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'FR', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'GR', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'HR', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'HU', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'IE', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'IT', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'LT', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'LU', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'LV', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'MT', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'NL', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'PL', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'PT', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'SE', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'SI', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'SK', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`name`, `description`, `updated_at`, `created_at`) VALUES
('Standard', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('Reduced', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('Low', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `name`, `description`, `rate`, `rule_companies_with_tax_id`, `rule_companies_without_tax_id`, `rule_individuals_with_tax_id`, `rule_individuals_without_tax_id`, `updated_at`, `created_at`) VALUES
(1, @RO_VAT_ZONE, 'RO VAT 19%', '', 19.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(1, @EU_VAT_ZONE, 'RO VAT 19%', '', 19.0000, 0, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, @RO_VAT_ZONE, 'RO VAT 9%', '', 9.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, @EU_VAT_ZONE, 'RO VAT 9%', '', 9.0000, 0, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, @RO_VAT_ZONE, 'RO VAT 5%', '', 5.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, @EU_VAT_ZONE, 'RO VAT 5%', '', 5.0000, 0, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);