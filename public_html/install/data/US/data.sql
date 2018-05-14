INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('US Tax Zone', 'Tax zone for USA', NOW(), NOW());
-- --------------------------------------------------------
SET @US_VAT_ZONE = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@US_VAT_ZONE, 'US', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_classes` (`name`, `description`, `date_updated`, `date_created`) VALUES
('Standard', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) VALUES
(1, @US_VAT_ZONE, 'percent', 'TAX 10%', '', 10, 'both', 'both', NOW(), NOW());
