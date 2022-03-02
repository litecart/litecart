INSERT INTO `lc_languages` (`status`, `code`, `code2`, `name`, `locale`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'nb', 'nob', 'Norsk (Bokmål)', 'nb_NO.utf8,nb_NO.UTF-8,norwegian', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%b %e %Y', '%H:%M', '%b %e %Y %H:%M', ',', ' ', '', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_translations` ADD `text_nb` text NOT NULL AFTER `text_en`;
-- --------------------------------------------------------
INSERT INTO `lc_currencies` (`status`, `code`, `number`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'NOK', '578', 'Norske kroner', 1.00, 2, 'kr. ', '', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_products_prices` ADD `NOK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_campaigns` ADD `NOK` DECIMAL(11,4) NOT NULL;
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 'NOK' WHERE `key` in ('site_currency_code', 'default_currency_code');
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `value` = 8.0364 WHERE `code` = 'USD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `value` = 9.4268 WHERE `code` = 'EUR' LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('NO VAT Zone', '', NOW(), NOW());
-- --------------------------------------------------------
SET @NO_VAT_ZONE = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@NO_VAT_ZONE, 'NO', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_classes` (`name`, `description`, `date_updated`, `date_created`) VALUES
('Standard', '', NOW(), NOW()),
('Food', '', NOW(), NOW()),
('Cultural', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `name`, `description`, `rate`, `rule_companies_with_tax_id`, `rule_companies_without_tax_id`, `rule_individuals_with_tax_id`, `rule_individuals_without_tax_id`, `date_updated`, `date_created`) VALUES
(1, @NO_VAT_ZONE, 'NO VAT 25%', '', 25.0000, 1, 1, 1, 1, NOW(), NOW()),
(1, @NO_VAT_ZONE, 'NO VAT 15%', '', 15.0000, 1, 1, 1, 1, NOW(), NOW()),
(1, @NO_VAT_ZONE, 'NO VAT 10%', '', 10.0000, 1, 1, 1, 1, NOW(), NOW());
