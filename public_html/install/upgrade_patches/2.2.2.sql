ALTER TABLE `lc_order_statuses`
ADD INDEX(`is_archived`),
ADD INDEX(`is_sale`);
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('checkout', 'local', 'Send Order Confirmation', 'Send order confirmations via email.', 'send_order_confirmation', '1', 'toggle("y/n")', 11, NOW(), NOW());
