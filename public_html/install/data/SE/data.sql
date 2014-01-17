INSERT INTO `lc_currencies` (`status`, `code`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'SEK', 'Svenska kronor', 1.00, 2, '', ' kr', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_products_prices`
ADD `SEK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_campaigns`
ADD `SEK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_options`
ADD `SEK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `value` = 'SEK'
WHERE `key` in ('store_currency_code', 'default_currency_code');
-- --------------------------------------------------------
UPDATE `lc_currencies`
SET `value` = 0.153
WHERE `code` = 'USD'
LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_currencies`
SET `value` = 0.112
WHERE `code` = 'EUR'
LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_languages` (`status`, `code`, `name`, `locale`, `charset`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'sv', 'Svenska', 'sv_SE.utf8,sv_SE.UTF-8,swedish', 'UTF-8', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%b %e %Y', '%H:%M', '%b %e %Y %H:%M', ',', ' ', '', 0, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('SE VAT Zone', '', NOW(), NOW());
-- --------------------------------------------------------
SET @TAX_ZONE_SE = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@TAX_ZONE_SE, 'SE', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_classes` (`name`, `description`, `date_updated`, `date_created`) VALUES
('Standard', '', NOW(), NOW()),
('Reduced', '', NOW(), NOW()),
('Groceries', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) VALUES
(1, @TAX_ZONE_SE, 'percent', 'SE VAT 25%', '', 25.0000, 'both', 'both', NOW(), NOW()),
(2, @TAX_ZONE_SE, 'percent', 'SE VAT 12%', '', 12.0000, 'both', 'both', NOW(), NOW()),
(3, @TAX_ZONE_SE, 'percent', 'SE VAT 6%', '', 6.0000, 'both', 'both', NOW(), NOW());
