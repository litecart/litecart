ALTER TABLE `lc_languages`
ADD COLUMN `url_type` VARCHAR(16) NOT NULL DEFAULT 'path' AFTER `charset`,
ADD COLUMN `domain_name` VARCHAR(64) NOT NULL DEFAULT '' AFTER `url_type`;
-- --------------------------------------------------------
DELETE FROM `lc_settings` WHERE `key` = 'seo_links_language_prefix';
-- --------------------------------------------------------
ALTER TABLE `lc_users`
CHANGE COLUMN `permissions` `apps` VARCHAR(4096) NOT NULL DEFAULT '';
ADD `widgets` VARCHAR(512) NOT NULL DEFAULT '' AFTER `apps`;