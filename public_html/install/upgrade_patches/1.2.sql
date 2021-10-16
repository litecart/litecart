ALTER TABLE `lc_languages` DROP mysql_collation;
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('listings', 'local', 'Similar Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_similar_products_num_items', '10', 'int()', 15, NOW(), NOW()),
('listings', 'local', 'Recently Viewed Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_recently_viewed_products_num_items', '4', 'int()', 16, NOW(), NOW()),
('listings', 'local', 'Latest Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_latest_products_num_items', '10', 'int()', 17, NOW(), NOW()),
('listings', 'local', 'Most Popular Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_most_popular_products_num_items', '10', 'int()', 18, NOW(), NOW()),
('listings', 'local', 'Campaign Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_campaign_products_num_items', '5', 'int()', 19, NOW(), NOW());
