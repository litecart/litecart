UPDATE `lc_settings` SET setting_group_key = 'images', priority = 1 WHERE `key` = 'cache_clear_thumbnails' LIMIT 1;
-- --------------------------------------------------------
ALTER TABLE `lc_translations`
ADD COLUMN `backend` TINYINT(1) NOT NULL AFTER `pages`,
ADD COLUMN `frontend` TINYINT(1) NOT NULL AFTER `backend`,
CHANGE COLUMN `date_updated` `date_updated` DATETIME NOT NULL AFTER `date_accessed`,
ADD INDEX `backend` (`backend`),
ADD INDEX `frontend` (`frontend`),
ADD INDEX `date_updated` (`date_updated`),
ADD INDEX `date_created` (`date_created`);
-- --------------------------------------------------------
ALTER TABLE `lc_translations`
DROP COLUMN `pages`,
DROP COLUMN `date_accessed`
