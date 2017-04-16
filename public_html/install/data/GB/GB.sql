INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'GBP', '826', 'British Pound', 1.00, 2, '£', '', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_products_prices` ADD `GBP` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_campaigns` ADD `GBP` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_options` ADD `GBP` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 'GBP' WHERE `key` in ('store_currency_code', 'default_currency_code');
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `value` = 1.2493 WHERE `code` = 'USD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `value` = 1.1790 WHERE `code` = 'EUR' LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('GB VAT Zone', '', NOW(), NOW());
-- --------------------------------------------------------
SET @GB_VAT_ZONE = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@GB_VAT_ZONE, 'GB', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('EU VAT Zone', 'European Union excl. GB', NOW(), NOW());
-- --------------------------------------------------------
SET @EU_VAT_ZONE = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@EU_VAT_ZONE, 'AT', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'BE', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'BG', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'CY', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'CZ', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'DE', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'DK', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'EE', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'ES', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'FR', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'FI', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'GR', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'HR', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'HU', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'IE', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'IT', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'LV', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'LT', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'LU', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'MT', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'NL', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'PL', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'PT', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'RO', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'SE', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'SI', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'SK', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_classes` (`name`, `description`, `date_updated`, `date_created`) VALUES
('Standard', '', NOW(), NOW()),
('Reduced', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) VALUES
(1, @GB_VAT_ZONE, 'percent', 'UK VAT 20%', '', 20.0000, 'both', 'both', NOW(), NOW()),
(1, @EU_VAT_ZONE, 'percent', 'UK VAT 20%', '', 20.0000, 'individuals', 'both', NOW(), NOW()),
(1, @EU_VAT_ZONE, 'percent', 'UK VAT 20%', '', 20.0000, 'companies', 'without', NOW(), NOW()),
(2, @GB_VAT_ZONE, 'percent', 'UK VAT 5%', '', 5.0000, 'both', 'both', NOW(), NOW()),
(2, @EU_VAT_ZONE, 'percent', 'UK VAT 5%', '', 5.0000, 'individuals', 'both', NOW(), NOW()),
(2, @EU_VAT_ZONE, 'percent', 'UK VAT 5%', '', 5.0000, 'companies', 'without', NOW(), NOW());
