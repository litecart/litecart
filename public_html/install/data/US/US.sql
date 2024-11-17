INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('US Tax Zone', 'Tax zone for USA', NOW(), NOW());
-- -----
SET @US_VAT_ZONE = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@US_VAT_ZONE, 'US', '', NOW(), NOW());
-- -----
INSERT INTO `lc_tax_classes` (`name`, `description`, `date_updated`, `date_created`) VALUES
('Standard', '', NOW(), NOW());
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `name`, `description`, `rate`, `rule_companies_with_tax_id`, `rule_companies_without_tax_id`, `rule_individuals_with_tax_id`, `rule_individuals_without_tax_id`, `date_updated`, `date_created`) VALUES
(1, @US_VAT_ZONE, 'TAX 10%', '', 10, 1, 1, 1, 1, NOW(), NOW());
-- -----
UPDATE `lc_settings`
SET `value` = 'US-Letter'
WHERE `key` = 'default_print_paper_size'
LIMIT 1;
