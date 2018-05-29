INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('CA Tax Zone', 'Tax zone for Canada', NOW(), NOW());
-- --------------------------------------------------------
SET @CA_VAT_ZONE = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@CA_VAT_ZONE, 'CA', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_classes` (`name`, `description`, `date_updated`, `date_created`) VALUES
('Standard', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) VALUES
(1, @CA_VAT_ZONE, 'percent', 'Tax 13%', '', 13, 'both', 'both', NOW(), NOW());
