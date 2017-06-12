DELETE FROM `lc_zones` WHERE country_code = 'US' AND code IN ('AS','AF','AA','AC','AE','AM','AP','DC','FM','GU','MH','MP','PW','PR','VI');
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '[a-zA-Z]{1,2}[0-9][0-9a-zA-Z]? ?[0-9][a-zA-Z]{2}' WHERE iso_code_2 = 'GB';
-- --------------------------------------------------------
DELETE FROM `lc_settings_groups` WHERE `key` = 'general';
-- --------------------------------------------------------
UPDATE `lc_settings` SET setting_group_key = 'store_info' WHERE setting_group_key = 'general';
-- --------------------------------------------------------
DELETE FROM `lc_settings` WHERE `key` IN ('job_modules', 'customer_modules', 'shipping_modules', 'payment_modules', 'order_modules', 'order_total_modules');
-- --------------------------------------------------------
UPDATE `lc_settings` SET title = 'Order Copy Recipients' WHERE `key` = 'email_order_copy';
-- --------------------------------------------------------
UPDATE `lc_settings` SET description = 'Force guests to create an account.' WHERE `key` = 'register_guests';
