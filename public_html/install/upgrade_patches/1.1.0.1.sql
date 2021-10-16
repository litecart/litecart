ALTER TABLE `lc_products` CHANGE `image` `image` VARCHAR(256);
-- --------------------------------------------------------
UPDATE `lc_settings` set `value` = '0' where `key`= 'regional_settings_screen_enabled';
