UPDATE `lc_countries` set iso_code_1 = '626' WHERE iso_code_2 = 'TP' LIMIT 1;
-- -----
UPDATE `lc_countries` set iso_code_1 = '530' WHERE iso_code_2 = 'AN' LIMIT 1;
-- -----
ALTER TABLE `lc_attribute_values`
ADD COLUMN `priority` INT(11) NOT NULL AFTER `group_id`;
-- -----
UPDATE `lc_settings_groups`
SET `name` = 'Customer Details'
WHERE `key` = 'customer_details'
LIMIT 1;
-- -----
DELETE FROM `lc_translations`
WHERE `code` = 'settings_group:title_customer_details'
LIMIT 1;
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('listings', 'global', 'Show Product Count In Category Tree', 'Show the number of products inside each category in the category tree.', 'category_tree_product_count', '0', 'toggle("y/n")', 22, NOW(), NOW());
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `key`, `title`, `description`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('customer_details', 'global', 'accounts_enabled', 'Enable Customer Accounts', 'Allow customers to create an account and sign in.', '1', 'toggle("y/n")', 11, NOW(), NOW()),
('customer_details', 'local', 'customer_field_zone', 'Zone/State/Province Field', 'Display the field for the customer\'s zone/state.', '1', 'toggle("y/n")', 23, NOW(), NOW()),
('images', 'local', 'webp_enabled', 'WebP Enabled', 'Use WebP images if supported by the browser.', '0', 'toggle("e/d")', 44, NOW(), NOW());
-- -----
ALTER TABLE `lc_countries` ADD UNIQUE INDEX `iso_code_1` (`iso_code_1`);
-- -----
ALTER TABLE `lc_currencies` CHANGE COLUMN `id` `id` TINYINT(2) NOT NULL AUTO_INCREMENT FIRST;
-- -----
ALTER TABLE `lc_languages` CHANGE COLUMN `id` `id` TINYINT(2) NOT NULL AUTO_INCREMENT FIRST;
-- -----
INSERT IGNORE INTO `lc_modules` (`module_id`, `type`, `status`, `priority`, `settings`, `date_updated`, `date_created`) VALUES
('job_cache_cleaner', 'job', 1, 0, '{"status":"1","priority":"0"}', NOW(), NOW()),
('job_mysql_optimizer', 'job', 1, 0, '{"status":"1","frequency":"monthly","priority":"0"}', NOW(), NOW());
