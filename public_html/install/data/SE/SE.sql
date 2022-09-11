INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `charset`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'sv', 'swe', 'Svenska', 'sv_SE.utf8,sv_SE.UTF-8,swedish', 'UTF-8', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%b %e %Y', '%H:%M', '%b %e %Y %H:%M', ',', ' ', '', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_translations` ADD `text_sv` text NOT NULL AFTER `text_en`;
-- --------------------------------------------------------
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'SEK', '752', 'Svenska kronor', 1.00, 2, '', ' kr', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_products_prices` ADD `SEK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_campaigns` ADD `SEK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_options_values` ADD `SEK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 'SEK' WHERE `key` in ('store_currency_code', 'default_currency_code');
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `value` = 9.76 WHERE `code` = 'USD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `value` = 10.68 WHERE `code` = 'EUR' LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('SE VAT Zone', '', NOW(), NOW());
-- --------------------------------------------------------
SET @SE_VAT_ZONE = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@SE_VAT_ZONE, 'SE', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('EU VAT Zone', 'European Union excl. SE', NOW(), NOW());
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
(@EU_VAT_ZONE, 'SI', '', NOW(), NOW()),
(@EU_VAT_ZONE, 'SK', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_classes` (`name`, `description`, `date_updated`, `date_created`) VALUES
('Standard', '', NOW(), NOW()),
('Reduced', '', NOW(), NOW()),
('Groceries', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `rule_companies_with_tax_id`, `rule_companies_without_tax_id`, `rule_individuals_with_tax_id`, `rule_individuals_without_tax_id`, `date_updated`, `date_created`) VALUES
(1, @SE_VAT_ZONE, 'percent', 'SE VAT 25%', '', 25.0000, 1, 1, 1, 1, NOW(), NOW()),
(1, @EU_VAT_ZONE, 'percent', 'SE VAT 25%', '', 25.0000, 0, 1, 1, 1, NOW(), NOW()),
(2, @SE_VAT_ZONE, 'percent', 'SE VAT 6%', '', 6.0000, 1, 1, 1, 1, NOW(), NOW()),
(2, @EU_VAT_ZONE, 'percent', 'SE VAT 6%', '', 6.0000, 0, 1, 1, 1, NOW(), NOW()),
(3, @SE_VAT_ZONE, 'percent', 'SE VAT 12%', '', 12.0000, 1, 1, 1, 1, NOW(), NOW()),
(3, @EU_VAT_ZONE, 'percent', 'SE VAT 12%', '', 12.0000, 0, 1, 1, 1, NOW(), NOW());
