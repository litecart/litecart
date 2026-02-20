INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `charset`, `raw_date`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `currency_size`, `image`, `direction`, `date_created`, `date_updated`) VALUES
('1', 'ko', 'kor', '한국어', 'ko_KR.UTF-8', 'UTF-8', '%Y년 %m월 %e일', '%Y. %m. %e.', '%H:%M', '%Y. %m. %e. %H:%M', '.', ',', 'KRW', '0', '', 'ltr', NOW(), NOW());
-- -----
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`) VALUES
('1', 'KRW', '410', 'South Korean Won', '1.00000000', '0', '₩', '', '1', NOW());
-- -----
ALTER TABLE `lc_products` ADD `price_krw` DECIMAL(11,4) NOT NULL AFTER `price_usd`;
-- -----
ALTER TABLE `lc_products` ADD `campaign_price_krw` DECIMAL(11,4) NOT NULL AFTER `campaign_price_usd`;
-- -----
UPDATE `lc_settings` SET `value` = 'KRW' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'KRW' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00075000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00070000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `date_created`, `date_updated`) VALUES
('KR_VAT', 'KR VAT Zone', 'South Korea', NOW(), NOW());
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_created`) VALUES
(@VAT_ZONE_ID, 'KR', '', NOW());
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `date_created`, `date_updated`) VALUES
('KR_VAT', 'South Korea VAT', 'Standard 10% VAT rate', NOW(), NOW());
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `date_created`, `date_updated`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'South Korea VAT', '10%', '10.00000', NOW(), NOW());