INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `locale_intl`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `priority`, `updated_at`, `created_at`) VALUES
(1, 'en', 'eng', 'English', 'en_ZA.utf8,en_ZA.UTF-8,english', 'en_ZA', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%b %e %Y', '%H:%M', '%b %e %Y %H:%M', '.', ',', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(1, 'af', 'afr', 'Afrikaans', 'af_ZA.utf8,af_ZA.UTF-8,afrikaans', 'af_ZA', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%b %e %Y', '%H:%M', '%b %e %Y %H:%M', '.', ',', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_translations` ADD `text_af` text NOT NULL AFTER `text_en`;
-- -----
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `updated_at`, `created_at`) VALUES
(1, 'ZAR', '710', 'Rand', 1.00, 2, 'R ', '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_products_prices` ADD `ZAR` DECIMAL(11,4) NOT NULL;
-- -----
UPDATE `lc_settings` SET `value` = 'ZAR' WHERE `key` in ('store_currency_code', 'default_currency_code');
-- -----
UPDATE `lc_currencies` SET `value` = 0.0555 WHERE `code` = 'USD' LIMIT 1;
-- -----
UPDATE `lc_currencies` SET `value` = 0.0651 WHERE `code` = 'EUR' LIMIT 1;
-- -----
INSERT INTO `lc_geo_zones` (`name`, `description`, `updated_at`, `created_at`) VALUES
('ZA Tax Zone', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @ZA_TAX_ZONE = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `updated_at`, `created_at`) VALUES
(@ZA_TAX_ZONE, 'ZA', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`name`, `description`, `updated_at`, `created_at`) VALUES
('Standard (VAT)', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `name`, `description`, `rate`, `rule_companies_with_tax_id`, `rule_companies_without_tax_id`, `rule_individuals_with_tax_id`, `rule_individuals_without_tax_id`, `updated_at`, `created_at`) VALUES
(1, @ZA_TAX_ZONE, 'ZA VAT 15%', '', 15.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);