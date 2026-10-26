INSERT INTO `lc_settings`
(`setting_group_key`, `type`, `key`, `title`, `description`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('images', 'global', 'jxl_enabled', 'JXL Enabled', 'Use JPEG XL images if supported by the browser.', '0', 'toggle("e/d")', 44, NOW(), NOW());
