UPDATE `lc_settings` SET setting_group_key = 'images', priority = 1 WHERE `key` = 'cache_clear_thumbnails' LIMIT 1;
-- -----
ALTER TABLE `lc_translations`
ADD COLUMN `frontend` TINYINT(1) NOT NULL AFTER `html`,
ADD COLUMN `backend` TINYINT(1) NOT NULL AFTER `frontend`,
CHANGE COLUMN `date_updated` `date_updated` DATETIME NOT NULL AFTER `date_accessed`,
ADD INDEX `frontend` (`frontend`),
ADD INDEX `backend` (`backend`),
ADD INDEX `date_updated` (`date_updated`),
ADD INDEX `date_created` (`date_created`);
-- -----
ALTER TABLE `lc_translations`
DROP COLUMN `pages`,
DROP COLUMN `date_accessed`
