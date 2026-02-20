INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`) VALUES
('1', 'DOP', '214', 'Dominican Peso', '1.00000000', '2', 'RD$', '', '1', NOW());
-- -----
ALTER TABLE `lc_products` ADD `price_dop` DECIMAL(11,4) NOT NULL AFTER `price_usd`;
-- -----
ALTER TABLE `lc_products` ADD `campaign_price_dop` DECIMAL(11,4) NOT NULL AFTER `campaign_price_usd`;
-- -----
UPDATE `lc_settings` SET `value` = 'DOP' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'DOP' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0168000000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.0156000000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `date_created`, `date_updated`) VALUES
('DO_VAT', 'DO VAT Zone', 'Dominican Republic', NOW(), NOW());
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_created`) VALUES
(@VAT_ZONE_ID, 'DO', '', NOW());
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `date_created`, `date_updated`) VALUES
('DO_ITBIS', 'Dominican Republic ITBIS', 'Standard 18% ITBIS rate', NOW(), NOW());
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `date_created`, `date_updated`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Dominican Republic ITBIS', '18%', '18.00000', NOW(), NOW());