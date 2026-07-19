INSERT INTO `lc_geo_zones` (`name`, `description`, `updated_at`, `created_at`) VALUES
('US Tax Zone', 'Tax zone for USA', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
SET @US_VAT_ZONE = LAST_INSERT_ID();
-- -----
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `updated_at`, `created_at`) VALUES
(@US_VAT_ZONE, 'US', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `name`, `description`, `rate`, `rule_companies_with_tax_id`, `rule_companies_without_tax_id`, `rule_individuals_with_tax_id`, `rule_individuals_without_tax_id`, `updated_at`, `created_at`) VALUES
(1, @US_VAT_ZONE, 'TAX 10%', '', 10, 1, 1, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
-- -----
UPDATE `lc_settings`
SET `value` = 'US-Letter'
WHERE `key` = 'default_print_paper_size'
LIMIT 1;