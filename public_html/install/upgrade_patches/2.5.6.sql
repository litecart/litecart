UPDATE `lc_modules`
SET `settings` = REPLACE(settings, 'report_frequency', 'frequency')
WHERE `key` = 'job_error_reporter'
LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `function` = 'select("1:1","2:3","3:2","3:4","4:3","16:9","9:16")'
WHERE `key` LIKE '%_image_ratio';
-- --------------------------------------------------------
ALTER TABLE `lc_users`
ADD COLUMN IF NOT EXISTS `date_expire_sessions` TIMESTAMP NULL AFTER `date_login`;
