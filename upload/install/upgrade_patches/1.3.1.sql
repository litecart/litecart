INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('', 'global', 'Jobs Last Push', 'Time when background jobs where last pushed for execution.', 'jobs_last_push', now(), 'input()', 0, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_cart_items` ADD COLUMN `cart_uid` VARCHAR(13) NOT NULL AFTER `customer_id`, ADD `key` VARCHAR(32) NOT NULL AFTER `cart_uid`, CHANGE `option_id` `options` VARCHAR(2048) NOT NULL, CHANGE `quantity` `quantity` DECIMAL(11, 4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_cart_items` ADD INDEX `cart_uid` (`cart_uid`);
-- --------------------------------------------------------
ALTER TABLE `lc_products` CHANGE `quantity` `quantity` DECIMAL(11, 4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_options_stock` CHANGE `quantity` `quantity` DECIMAL(11, 4) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_products_info` DROP COLUMN `meta_keywords`;
-- --------------------------------------------------------
ALTER TABLE `lc_categories_info` DROP COLUMN `meta_keywords`;
-- --------------------------------------------------------
ALTER TABLE `lc_manufacturers_info` DROP COLUMN `meta_keywords`;
-- --------------------------------------------------------
ALTER TABLE `lc_pages_info` DROP COLUMN `meta_keywords`;
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = concat('fa-', icon);
-- --------------------------------------------------------
INSERT INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES ('images', 'Images', 'Settings for graphical elements', 80);
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('images', 'local', 'Category Images: Aspect Ratio', 'The aspect ratio of the category thumbnails', 'category_image_ratio', '16:9', 'select("1:1","2:3","3:2","3:4","4:3","16:9")', '10', NOW(), NOW()),
('images', 'local', 'Product Images: Aspect Ratio', 'The aspect ratio of the product thumbnails', 'product_image_ratio', '1:1', 'select("1:1","2:3","3:2","3:4","4:3","16:9")', '30', NOW(), NOW()),
('images', 'local', 'Product Images: Clipping Method', 'The clipping method used for scaled product thumbnails.', 'product_image_clipping', 'CROP', 'select("CROP","FIT","FIT_USE_WHITESPACING")', '31', NOW(), NOW()),
('images', 'local', 'Product Images: Watermark', 'Watermark product images with the store logo.', 'product_image_watermark', '0', 'toggle("y/n")', '33', NOW(), NOW()),
('images', 'local', 'Downsample', 'Downsample large uploaded images to best fit within the given dimensions of "width,height" or leave empty. Default: 2048,2048', 'image_downsample_size', '2048,2048', 'smallinput()', '34', NOW(), NOW()),
('images', 'local', 'Image Quality', 'The JPEG quality for uploaded images (0-100). Default: 90', 'image_quality', '90', 'int()', '40', NOW(), NOW()),
('images', 'local', 'Thumbnail Quality', 'The JPEG quality for thumbnail images (0-100). Default: 65', 'image_thumbnail_quality', '65', 'int()', '41', NOW(), NOW()),
('images', 'local', 'Whitespace Color', 'Set the color of any generated whitespace to the given RGB value. Default: 255,255,255', 'image_whitespace_color', '255,255,255', 'smallinput()', '42', NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_settings_groups` ADD UNIQUE (`key`);
-- --------------------------------------------------------
ALTER TABLE `lc_settings` ADD UNIQUE (`key`);