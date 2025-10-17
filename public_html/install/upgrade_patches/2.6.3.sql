ALTER TABLE `lc_products`
ADD INDEX `date_created` (`date_created`);
-- -----
ALTER TABLE `lc_products_campaigns`
ADD INDEX `start_date` (`start_date`),
ADD INDEX `end_date` (`end_date`);
-- -----
ALTER TABLE `lc_products_to_categories`
ADD INDEX `category_id` (`category_id`);
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('defaults', 'local', 'Default Print Paper Size', 'Default paper size used for printing.', 'default_print_paper_size', 'A4', 'select("A5","A5R","A4","A4R","US-Letter")', 30, NOW(), NOW());
