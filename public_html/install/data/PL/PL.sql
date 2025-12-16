INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `locale_intl`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `priority`, `updated_at`, `created_at`) VALUES
(1, 'pl', 'pol', 'Polski', 'pl_PL.utf8,pl_PL.UTF-8,polish', 'pl_PL', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%b %e %Y', '%H:%M', '%b %e %Y %H:%M', ',', ' ', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_translations` ADD `text_pl` text NOT NULL AFTER `text_en`;
-- -----
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `updated_at`, `created_at`) VALUES
(1, 'PLN', '985', 'Złoty', 1.00, 2, '', ' zł', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_products_prices` ADD `PLN` DECIMAL(11,4) NOT NULL;
-- -----
UPDATE `lc_settings` SET `value` = 'PLN' WHERE `key` in ('store_currency_code', 'default_currency_code');
-- -----
UPDATE `lc_currencies` SET `value` = 3.86 WHERE `code` = 'USD' LIMIT 1;
-- -----
UPDATE `lc_currencies` SET `value` = 4.53 WHERE `code` = 'EUR' LIMIT 1;
-- -----
INSERT INTO `lc_geo_zones` (`name`, `description`, `updated_at`, `created_at`) VALUES
('PL VAT Zone', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @PL_VAT_ZONE = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `updated_at`, `created_at`) VALUES
(@PL_VAT_ZONE, 'PL', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_geo_zones` (`name`, `description`, `updated_at`, `created_at`) VALUES
('EU VAT Zone', 'European Union excl. PL', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
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
(@EU_VAT_ZONE, 'PT', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(@EU_VAT_ZONE, 'RO', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
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
(1, @PL_VAT_ZONE, 'PL VAT 23%', '', 23.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(1, @EU_VAT_ZONE, 'PL VAT 23%', '', 23.0000, 0, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, @PL_VAT_ZONE, 'PL VAT 8%', '', 8.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, @EU_VAT_ZONE, 'PL VAT 8%', '', 8.0000, 0, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, @PL_VAT_ZONE, 'PL VAT 5%', '', 5.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, @EU_VAT_ZONE, 'PL VAT 5%', '', 5.0000, 0, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);