UPDATE `lc_countries`
SET `postcode_format` = '[0-9]{5}'
WHERE `iso_code_2` = 'NI'
LIMIT 1;
