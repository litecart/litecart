UPDATE `lc_countries`
SET `postcode_format` = '[0-9]{5}'
WHERE `iso_code_2` = 'NI'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `function` = 'zone("store_country_code")'
WHERE `key` = 'store_zone_code'
LIMIT 1;
