UPDATE `lc_settings` SET `value` = 4 WHERE `key` = 'box_recently_viewed_products_num_items' LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES
('email', 'Email', 'Email and SMTP', 30);
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('email', 'local', 'SMTP Enabled', 'Wheither or not to use an SMTP server for delivering email.', 'smtp_status', '0', 'toggle("e/d")', 10, NOW(), NOW()),
('email', 'local', 'SMTP Host', 'SMTP hostname e.g. smtp.myprovider.com.', 'smtp_host', 'localhost', 'input()', 11, NOW(), NOW()),
('email', 'local', 'SMTP Port', 'SMTP port e.g. 25, 465 (SSL/TLS), or 587 (STARTTLS).', 'smtp_port', '25', 'number()', 12, NOW(), NOW()),
('email', 'local', 'SMTP Username', 'Username for SMTP authentication.', 'smtp_username', '', 'input()', 13, NOW(), NOW()),
('email', 'local', 'SMTP Password', 'Password for SMTP authentication.', 'smtp_password', '', 'input()', 14, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_customers`
CHANGE COLUMN `postcode` `postcode` VARCHAR(16) NOT NULL COLLATE 'utf8_swedish_ci',
CHANGE COLUMN `shipping_postcode` `shipping_postcode` VARCHAR(16) NOT NULL COLLATE 'utf8_swedish_ci';
-- --------------------------------------------------------
ALTER TABLE `lc_orders`
CHANGE COLUMN `customer_postcode` `customer_postcode` VARCHAR(16) NOT NULL COLLATE 'utf8_swedish_ci',
CHANGE COLUMN `shipping_postcode` `shipping_postcode` VARCHAR(16) NOT NULL COLLATE 'utf8_swedish_ci';
