INSERT IGNORE INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES ('customer_details', 'Customer Details', 'Settings for customer details.', '45');
-- --------------------------------------------------------
INSERT IGNORE INTO `lc_settings` (`setting_group_key`, `key`, `title`, `description`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('customer_details', 'customer_field_company', 'Company Field', 'Display the field for the customer\'s company name.', '1', 'toggle("y/n")', 21, NOW(), NOW()),
('customer_details', 'customer_field_tax_id', 'Tax ID Field', 'Display the field for the customer\'s tax ID.', '1', 'toggle("y/n")', 22, NOW(), NOW());
-- --------------------------------------------------------
UPDATE `lc_settings` SET
  `key` = 'regional_settings_screen',
  `setting_group_key` = 'customer_details',
  `priority` = 10
WHERE `key` = 'regional_settings_screen_enabled'
LIMIT 1;
