ALTER TABLE `lc_customers`
ADD COLUMN `last_ip` VARCHAR(39) NOT NULL AFTER `password_reset_token`,
ADD COLUMN `last_agent` VARCHAR(256) NOT NULL AFTER `last_ip`,
ADD COLUMN `date_login` DATETIME NOT NULL AFTER `last_ip`;
-- --------------------------------------------------------
ALTER TABLE `lc_orders`
ADD COLUMN `user_agent` VARCHAR(256) NOT NULL AFTER `client_ip`,
ADD COLUMN `domain` VARCHAR(64) NOT NULL AFTER `user_agent`;
-- --------------------------------------------------------
ALTER TABLE `lc_modules`
ADD COLUMN `date_pushed` DATETIME NOT NULL AFTER `last_log`;
-- --------------------------------------------------------
DELETE FROM `lc_settings`
WHERE `key` = 'job_error_reporter:last_run';
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('email', 'Send Emails', 'Wheither or not the platform should deliver outgoing emails.', 'email_status', '1', 'toggle("y/n")', '1', NOW(), NOW());
