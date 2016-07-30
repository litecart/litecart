INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('', 'local', 'Platform Database Version', 'The platform version of the database', 'platform_database_version', '1.3.6', '', 0, NOW(), NOW());
-- --------------------------------------------------------
DELETE FROM `lc_settings` where `key` = 'set_currency_by_language';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `setting_group_key` = 'listings', `priority` = 1 WHERE `key` = 'catalog_only_mode';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `setting_group_key` = 'general' WHERE `key` = 'regional_settings_screen_enabled';