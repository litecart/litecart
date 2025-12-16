INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`) VALUES
('1', 'XOF', '952', 'West African CFA Franc', '1.00000000', '0', '', ' CFA', '1', NOW());
-- -----
ALTER TABLE `lc_products` ADD `price_xof` DECIMAL(11,4) NOT NULL AFTER `price_usd`;
-- -----
ALTER TABLE `lc_products` ADD `campaign_price_xof` DECIMAL(11,4) NOT NULL AFTER `campaign_price_usd`;
-- -----
UPDATE `lc_settings` SET `value` = 'XOF' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'XOF' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0016500000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0015300000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `date_created`, `date_updated`) VALUES
('CI_VAT', 'CI VAT Zone', 'Ivory Coast', NOW(), NOW());
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_created`) VALUES
(@VAT_ZONE_ID, 'CI', '', NOW());
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `date_created`, `date_updated`) VALUES
('CI_VAT', 'Ivory Coast VAT', 'Standard 18% VAT rate', NOW(), NOW());
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `date_created`, `date_updated`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Ivory Coast VAT', '18%', '18.00000', NOW(), NOW());