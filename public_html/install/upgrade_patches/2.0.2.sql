DELETE FROM `lc_zones` WHERE country_code = 'US' AND code IN ('AS','AF','AA','AC','AE','AM','AP','DC','FM','GU','MH','MP','PW','PR','VI');
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '' WHERE country_code = 'GB';
-- --------------------------------------------------------
DELETE FROM `lc_settings_groups` WHERE `key` = 'general' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings` SET setting_group_key = 'store_info' WHERE setting_group_key = 'general';