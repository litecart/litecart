ALTER TABLE `lc_products` ADD  `default_category_id` INT( 11 ) NOT NULL AFTER `sold_out_status_id` ;
-- --------------------------------------------------------
ALTER TABLE `lc_products` ADD  KEY `default_category_id` (`default_category_id`);
-- --------------------------------------------------------
CREATE TABLE `lc_products_to_categories` (
   `product_id` int(11) NOT NULL,
   `category_id` int(11) NOT NULL,
   PRIMARY KEY(`product_id`, `category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE {DATABASE_COLLATION};