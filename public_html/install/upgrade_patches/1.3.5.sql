INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('listings', 'local', 'Auto Decimals', 'Show only decimals if there are any to display.', 'auto_decimals', '1', 'toggle("e/d")', 20, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_pages_info` CHANGE COLUMN `content` `content` MEDIUMTEXT NOT NULL AFTER `title`;
-- --------------------------------------------------------
ALTER TABLE `lc_zones` ADD INDEX `code` (`code`);
-- --------------------------------------------------------
ALTER TABLE `lc_slides`	CHANGE COLUMN `caption` `caption` TEXT NOT NULL;