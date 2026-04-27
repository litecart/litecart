INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `charset`, `raw_date`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `currency_size`, `image`, `direction`, `updated_at`, `created_at`) VALUES
('1', 'ur', 'urd', 'اردو', 'ur_PK.UTF-8', 'UTF-8', '%e %B %Y', '%e %b %Y', '%H:%M', '%e %b %Y %H:%M', '.', ',', 'PKR', '2', '', 'rtl', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `updated_at`) VALUES
('1', 'PKR', '586', 'Pakistani Rupee', '1.00000000', '2', '₨', '', '1', CURRENT_TIMESTAMP);
-- -----
ALTER TABLE `lc_products` ADD `price_pkr` DECIMAL(11,4) NOT NULL AFTER `price_usd`;
-- -----
ALTER TABLE `lc_products` ADD `campaign_price_pkr` DECIMAL(11,4) NOT NULL AFTER `campaign_price_usd`;
-- -----
UPDATE `lc_settings` SET `value` = 'PKR' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'PKR' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0036000000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0033000000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('PK_VAT', 'PK VAT Zone', 'Pakistan', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `created_at`) VALUES
(@VAT_ZONE_ID, 'PK', '', CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `updated_at`, `created_at`) VALUES
('PK_GST', 'Pakistan GST', 'Standard 18% GST rate', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `updated_at`, `created_at`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Pakistan GST', '18%', '18.00000', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);