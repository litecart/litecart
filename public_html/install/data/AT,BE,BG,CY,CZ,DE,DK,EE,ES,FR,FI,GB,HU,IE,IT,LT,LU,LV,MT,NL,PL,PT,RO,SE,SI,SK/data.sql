INSERT INTO `lc_geo_zones` (`name`, `description`, `date_updated`, `date_created`) VALUES
('European Union', 'All countries in the European Union', NOW(), NOW());
-- --------------------------------------------------------
SET @GEO_ZONE_EU = LAST_INSERT_ID();
-- --------------------------------------------------------
INSERT INTO `lc_zones_to_geo_zones` (`geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) VALUES
(@GEO_ZONE_EU, 'AT', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'BE', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'BG', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'CY', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'CZ', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'DE', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'DK', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'EE', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'ES', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'FR', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'FI', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'GB', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'GR', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'HR', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'HU', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'IE', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'IT', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'LT', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'LU', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'LV', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'MT', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'NL', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'PL', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'PT', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'RO', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'SE', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'SI', '', NOW(), NOW()),
(@GEO_ZONE_EU, 'SK', '', NOW(), NOW());