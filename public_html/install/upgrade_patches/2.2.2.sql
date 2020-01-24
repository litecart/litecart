ALTER TABLE `lc_order_statuses`
ADD INDEX(`is_archived`),
ADD INDEX(`is_sale`);
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('checkout', 'local', 'Send Order Confirmation', 'The name of your store.', 'send_order_confirmation', '1', 'text()', 11, NOW(), NOW());
