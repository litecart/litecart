INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`) VALUES
('1', 'COP', '170', 'Colombian Peso', '1.00000000', '2', '$', '', '1', NOW());
-- -----
ALTER TABLE `lc_products` ADD `price_cop` DECIMAL(11,4) NOT NULL AFTER `price_usd`;
-- -----
ALTER TABLE `lc_products` ADD `campaign_price_cop` DECIMAL(11,4) NOT NULL AFTER `campaign_price_usd`;
-- -----
UPDATE `lc_settings` SET `value` = 'COP' WHERE `key` = 'default_currency_code';
-- -----
UPDATE `lc_settings` SET `value` = 'COP' WHERE `key` = 'store_currency_code';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00024000' WHERE `code` = 'USD';
-- -----
UPDATE `lc_currencies` SET `value` = '0.00022000' WHERE `code` = 'EUR';
-- -----
INSERT INTO `lc_geo_zones` (`code`, `name`, `description`, `date_created`, `date_updated`) VALUES
('CO_VAT', 'CO VAT Zone', 'Colombia', NOW(), NOW());
-- -----
SET @VAT_ZONE_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_created`) VALUES
(@VAT_ZONE_ID, 'CO', '', NOW());
-- -----
INSERT INTO `lc_tax_classes` (`code`, `name`, `description`, `date_created`, `date_updated`) VALUES
('CO_IVA', 'Colombia IVA', 'Standard 19% IVA rate', NOW(), NOW());
-- -----
SET @TAX_CLASS_ID = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `date_created`, `date_updated`) VALUES
(@TAX_CLASS_ID, @VAT_ZONE_ID, 'percent', 'Colombia IVA', '19%', '19.00000', NOW(), NOW());