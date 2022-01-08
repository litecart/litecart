ALTER TABLE `lc_products_campaigns`
CHANGE COLUMN `start_date` `start_date` TIMESTAMP NULL DEFAULT NULL,
CHANGE COLUMN `end_date` `end_date` TIMESTAMP NULL DEFAULT NULL;
-- --------------------------------------------------------
UPDATE `lc_countries`
SET postcode_format = '[0-9]{7}'
WHERE iso_code_2 = 'IL'
AND postcode_format = '[0-9]{5}'
LIMIT 1;
-- --------------------------------------------------------
UPDATE IGNORE `lc_customers` SET date_login = NULL WHERE date_login = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_customers` SET date_blocked_until = NULL WHERE date_blocked_until = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_customers` SET date_expire_sessions = NULL WHERE date_expire_sessions = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_emails` SET date_scheduled = NULL WHERE date_scheduled = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_emails` SET date_sent = NULL WHERE date_sent = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_modules` SET date_pushed = NULL WHERE date_pushed = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_modules` SET date_processed = NULL WHERE date_processed = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_products` SET date_valid_from = NULL WHERE date_valid_from = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_products` SET date_valid_to = NULL WHERE date_valid_to = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_products_campaigns` SET start_date = NULL WHERE start_date = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_products_campaigns` SET end_date = NULL WHERE end_date = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_slides` SET date_valid_from = NULL WHERE date_valid_from = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_slides` SET date_valid_to = NULL WHERE date_valid_to = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_translations` SET date_accessed = NULL WHERE date_accessed = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_users` SET date_valid_from = NULL WHERE date_valid_from = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_users` SET date_valid_to = NULL WHERE date_valid_to = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_users` SET date_active = NULL WHERE date_active = '0000-00-00 00:00:00';
-- --------------------------------------------------------
UPDATE IGNORE `lc_users` SET date_login = NULL WHERE date_login = '0000-00-00 00:00:00';