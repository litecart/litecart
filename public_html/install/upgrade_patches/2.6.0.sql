UPDATE `lc_modules`
SET `settings` = REPLACE(settings, 'report_frequency', 'frequency')
WHERE `module_id` = 'job_error_reporter'
LIMIT 1;
-- -----
UPDATE `lc_settings`
SET `function` = 'select("1:1","2:3","3:2","3:4","4:3","16:9","9:16")'
WHERE `key` LIKE '%_image_ratio';
-- -----
ALTER TABLE `lc_orders_comments`
ADD COLUMN `author_id` INT(11) NOT NULL DEFAULT '0' AFTER `order_id`;
-- -----
UPDATE `lc_pages`
SET dock = SUBSTRING_INDEX(dock, ',', 1);
-- -----
INSERT INTO `lc_settings`
(`setting_group_key`, `type`, `key`, `title`, `description`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('images', 'global', 'image_lazyload', 'Image Lazy Loading', 'Tells the browser to load images on the pages first when they are scrolled down to.', '1', 'toggle("e/d")', 45, NOW(), NOW()),
('images', 'global', 'avif_enabled', 'AVIF Enabled', 'Use AVIF images if supported by the browser.', '0', 'toggle("e/d")', 44, NOW(), NOW());
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('defaults', 'local', 'Default Print Paper Size', 'Default paper size used for printing.', 'default_print_paper_size', 'A4', 'select("A5","A5R","A4","A4R","US-Letter")', 30, NOW(), NOW());
