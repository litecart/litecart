INSERT INTO `lc_zones` (`country_code`, `code`, `name`) VALUES 
('CA', 'AB', 'Alberta'),
('CA', 'BC', 'British Columbia'),
('CA', 'MB', 'Manitoba'),
('CA', 'NB', 'New Brunswick'),
('CA', 'NL', 'Newfoundland and Labrador'),
('CA', 'NT', 'Northwest Territories'),
('CA', 'NS', 'Nova Scotia'),
('CA', 'NU', 'Nunavut'),
('CA', 'ON', 'Ontario'),
('CA', 'PE', 'Prince Edward Island'),
('CA', 'QC', 'Québec'),
('CA', 'SK', 'Saskatchewan'),
('CA', 'YT', 'Yukon Territory');
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('Canada', 'All states in Canada', NOW(), NOW());
-- --------------------------------------------------------
SET @GEO_ZONE_CANADA = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@GEO_ZONE_CANADA, 'CA', 'AB', NOW(), NOW()),
(@GEO_ZONE_CANADA, 'CA', 'BC', NOW(), NOW()),
(@GEO_ZONE_CANADA, 'CA', 'ON', NOW(), NOW()),
(@GEO_ZONE_CANADA, 'CA', 'QC', NOW(), NOW()),
(@GEO_ZONE_CANADA, 'CA', 'NS', NOW(), NOW()),
(@GEO_ZONE_CANADA, 'CA', 'NB', NOW(), NOW()),
(@GEO_ZONE_CANADA, 'CA', 'MB', NOW(), NOW()),
(@GEO_ZONE_CANADA, 'CA', 'PE', NOW(), NOW()),
(@GEO_ZONE_CANADA, 'CA', 'SK', NOW(), NOW()),
(@GEO_ZONE_CANADA, 'CA', 'NL', NOW(), NOW());