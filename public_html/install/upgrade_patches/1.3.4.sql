ALTER TABLE `lc_orders_items` CHANGE `quantity` `quantity` DECIMAL(11, 4) NOT NULL;
-- --------------------------------------------------------
DELETE FROM where `key` = 'contact_form_captcha_enabled';
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('security', 'local', 'CAPTCHA', 'Prevent robots from posting form data by enabling CAPTCHA security.', 'captcha_enabled', '1', 'toggle()', 15, NOW(), NOW());