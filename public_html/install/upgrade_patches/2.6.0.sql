ALTER TABLE `lc_orders_comments`
ADD COLUMN `author_id` INT(11) NOT NULL DEFAULT '0' AFTER `order_id`;
-- --------------------------------------------------------
UPDATE `lc_pages`
SET dock = CAST(REGEXP_REPLACE(dock, ',.*$', '') AS CHAR);
-- --------------------------------------------------------
INSERT INTO `lc_settings`
(`setting_group_key`, `type`, `key`, `title`, `description`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('images', 'global', 'image_lazyload', 'Image Lazy Loading', 'Tells the browser to load images on the pages first when they are scrolled down to.', '0', 'toggle("e/d")', 45, NOW(), NOW()),
('images', 'global', 'avif_enabled', 'AVIF Enabled', 'Use AVIF images if supported by the browser.', '0', 'toggle("e/d")', 44, NOW(), NOW());