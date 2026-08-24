DELETE t1 FROM `lc_products_prices` t1
JOIN `lc_products_prices` t2 ON t1.product_id = t2.product_id AND t1.id > t2.id;
-- -----
ALTER TABLE `lc_products_prices`
DROP KEY product_id,
ADD UNIQUE KEY product_id (product_id);
-- -----
ALTER TABLE `lc_products_images`
ADD UNIQUE INDEX `product_id_filename` (`product_id`, `filename`);
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('checkout', 'local', 'Withdrawal Window Days', 'The number of days a customer has to request a withdrawal after placing an order.', 'withdrawal_window_days', '14', 'number()', 14, NOW(), NOW());