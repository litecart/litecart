INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `charset`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'da', 'dan', 'Danske', 'da_DK.utf8,da_DK.UTF-8,danish', 'UTF-8', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%b %e %Y', '%H:%M', '%b %e %Y %H:%M', ',', ' ', '', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_translations` ADD `text_da` text NOT NULL AFTER `text_en`;
-- --------------------------------------------------------
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'DKK', '208', 'Danske kroner', 1.00, 2, 'kr. ', '', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_products_prices` ADD `DKK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_campaigns` ADD `DKK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_options` ADD `DKK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 'DKK' WHERE `key` in ('store_currency_code', 'default_currency_code');
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `value` = 0.1425 WHERE `code` = 'USD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `value` = 0.1345 WHERE `code` = 'EUR' LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('DK VAT Zone', '', NOW(), NOW());
-- --------------------------------------------------------
SET @DK_VAT_ZONE = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@DK_VAT_ZONE, 'DK', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('EU VAT Zone', 'European Union excl. DK', NOW(), NOW());
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
(@EU_VAT_ZONE, 'EE', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'ES', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'FR', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'FI', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'GB', '', NOW(), NOW()),
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
('Standard', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) VALUES
(1, @DK_VAT_ZONE, 'percent', 'DK VAT 25%', '', 25.0000, 'both', 'both', NOW(), NOW()),
(1, @EU_VAT_ZONE, 'percent', 'DK VAT 25%', '', 25.0000, 'individuals', 'both', NOW(), NOW()),
(1, @EU_VAT_ZONE, 'percent', 'DK VAT 25%', '', 25.0000, 'companies', 'without', NOW(), NOW());
