INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `charset`, `raw_date`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `currency_size`, `image`, `direction`, `updated_at`, `created_at`) VALUES
('1', 'tl', 'tgl', 'Tagalog', 'tl_PH.UTF-8', 'UTF-8', '%B %e, %Y', '%b %e, %Y', '%I:%M %p', '%b %e, %Y %I:%M %p', '.', ',', 'PHP', '2', '', 'ltr', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `updated_at`) VALUES
('1', 'PHP', '608', 'Philippine Peso', '1.00000000', '2', '₱', '', '1', CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_products` ADD `price_php` DECIMAL(11,4) NOT NULL AFTER `price_usd`;
-- -----
ALTER TABLE `lc_products` ADD `campaign_price_php` DECIMAL(11,4) NOT NULL AFTER `campaign_price_usd`;
-- -----
UPDATE `lc_settings` SET `value` = 'PHP' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'PHP' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0178000000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0165000000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('PH_VAT', 'PH VAT Zone', 'Philippines', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'PH', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('PH_VAT', 'Philippines VAT', 'Standard 12% VAT rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Philippines VAT', '12%', '12.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);