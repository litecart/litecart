INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `locale_intl`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `priority`, `updated_at`, `created_at`) VALUES
(1, 'ar', 'ara', 'العربية', 'ar_SA.utf8,ar_SA.UTF-8,arabic', 'ar_SA', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%b %e %Y', '%H:%M', '%b %e %Y %H:%M', '.', ',', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_translations` ADD `text_ar` text NOT NULL AFTER `text_en`;
-- -----
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `updated_at`, `created_at`) VALUES
(1, 'SAR', '682', 'Saudi Riyal', 1.00, 2, '', ' ر.س', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_products_prices` ADD `SAR` DECIMAL(11,4) NOT NULL;
-- -----
UPDATE `lc_settings` SET `value` = 'SAR' WHERE `key` in ('store_currency_code', 'default_currency_code');
-- -----
UPDATE `lc_currencies` SET `value` = 0.2666 WHERE `code` = 'USD' LIMIT 1;
-- -----
UPDATE `lc_currencies` SET `value` = 0.3127 WHERE `code` = 'EUR' LIMIT 1;
-- -----
INSERT INTO `lc_geo_zones` (`name`, `description`, `updated_at`, `created_at`) VALUES
('SA Tax Zone', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @SA_TAX_ZONE = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `updated_at`, `created_at`) VALUES
(@SA_TAX_ZONE, 'SA', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`name`, `description`, `updated_at`, `created_at`) VALUES
('Standard (VAT)', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `name`, `description`, `rate`, `rule_companies_with_tax_id`, `rule_companies_without_tax_id`, `rule_individuals_with_tax_id`, `rule_individuals_without_tax_id`, `updated_at`, `created_at`) VALUES
(1, @SA_TAX_ZONE, 'SA VAT 15%', '', 15.0000, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);