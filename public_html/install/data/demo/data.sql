INSERT INTO `lc_attribute_groups` (`id`, `code`, `sort`, `date_updated`, `date_created`) VALUES
(1, 'size', 'alphabetical', NOW(), NOW()),
(2, 'color', 'priority', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_attribute_groups_info` (`id`, `group_id`, `language_code`, `name`) VALUES
(1, 1, 'en', 'Size'),
(2, 2, 'en', 'Color');
-- --------------------------------------------------------
INSERT INTO `lc_attribute_values` (`id`, `group_id`, `date_updated`, `date_created`) VALUES
(1, 1, NOW(), NOW()),
(2, 1, NOW(), NOW()),
(3, 1, NOW(), NOW()),
(4, 2, NOW(), NOW()),
(5, 2, NOW(), NOW()),
(6, 2, NOW(), NOW()),
(7, 2, NOW(), NOW()),
(8, 2, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_attribute_values_info` (`id`, `value_id`, `language_code`, `name`) VALUES
(1, 1, 'en', 'Small'),
(2, 2, 'en', 'Medium'),
(3, 3, 'en', 'Large'),
(4, 4, 'en', 'Yellow'),
(5, 5, 'en', 'Green'),
(6, 6, 'en', 'Red'),
(7, 7, 'en', 'Blue'),
(8, 8, 'en', 'Purple');
-- --------------------------------------------------------
INSERT INTO `lc_categories` (`id`, `parent_id`, `status`, `code`, `list_style`, `keywords`, `image`, `priority`, `date_updated`, `date_created`) VALUES
(1, 0, 1, '', 'columns', '', '', 0, NOW(), NOW()),
(2, 1, 1, '', 'rows', '', '', 0, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_categories_info` (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `head_title`, `h1_title`, `meta_description`) VALUES
(1, 1, 'en', 'Rubber Ducks', '', '', '', '', ''),
(2, 2, 'en', 'Subcategory', '', '', '', '', '');
-- --------------------------------------------------------
INSERT INTO `lc_categories_filters` (`id`, `category_id`, `select_multiple`, `attribute_group_id`, `priority`) VALUES
(1, 1, 1, 1, 1);
-- --------------------------------------------------------
INSERT INTO `lc_customers` (`id`, `code`, `status`, `email`, `tax_id`, `company`, `firstname`, `lastname`, `address1`, `address2`, `postcode`, `city`, `country_code`, `zone_code`, `phone`, `different_shipping_address`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_zone_code`, `shipping_phone`, `date_updated`, `date_created`) VALUES
(1, '', 1, 'user@email.com', '0000000000', 'ACME Corp.', 'John', 'Doe', 'Longway Street 1', '', '12345', 'Newtown', 'US', 'CA', '1-555-123-4567', 0, '', '', '', '', '', '', '', '', '', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_manufacturers` (`id`, `status`, `featured`, `code`, `name`, `keywords`, `image`, `date_updated`, `date_created`) VALUES
(1, 1, 1, 'acme', 'ACME Corp.', '', 'manufacturers/1-acme-corp.png', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_manufacturers_info` (`id`, `manufacturer_id`, `language_code`, `short_description`, `description`, `h1_title`, `head_title`, `meta_description`, `link`) VALUES
(1, 1, 'en', '', '', '', '', '', '');
-- --------------------------------------------------------
INSERT INTO `lc_modules` (`module_id`, `type`, `status`, `priority`, `settings`, `last_log`, `date_updated`, `date_created`) VALUES
('sm_zone_weight', 'shipping', 0, 0, '{"status":"1","icon":"","weight_class":"kg","geo_zone_id_1":"","weight_rate_table_1":"","geo_zone_id_2":"","weight_rate_table_2":"","geo_zone_id_3":"","weight_rate_table_3":"","weight_rate_table_x":"5:8.95;10:15.95","method":">=","handling_fee":"0.00","tax_class_id":"1","priority":"0"}', '', NOW(), NOW()),
('pm_cod', 'payment', 0, 0, '{"status":"1","icon":"","fee":"5.00","tax_class_id":"1","order_status_id":"2","geo_zone_id":"","priority":"0"}', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_orders` (`id`, `uid`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_zone_code`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_zone_code`, `shipping_phone`, `shipping_option_id`, `shipping_option_name`, `shipping_tracking_id`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight_total`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) VALUES
(1, '585753da00024', 2, 1, 'ACME Corp.', 'John', 'Doe', 'user@email.com', '1-555-123-4567', '', 'Longway Street 1', '', 'Newtown', '12345', 'US', 'CA', 'ACME Corp.', 'John', 'Doe', 'Longway Street 1', '', 'Newtown', '12345', 'US', 'CA', '', 'sm_vendor:parcel', 'Domestic Parcel', '1112223334', 'pm_vendor:card', 'Card Payment', '123456789', 'en', '1.0000', 'kg', 'USD', 1, 7.2, 0, '0.0.0.0', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_orders_comments` (`id`, `order_id`, `author`, `text`, `hidden`, `date_created`) VALUES
(1, 1, 'customer', 'This is a message from the customer.', 0, NOW()),
(2, 1, 'staff', 'This is a message from the store crew.', 0, NOW()),
(3, 1, 'staff', 'This is a hidden message by the store crew.', 1, NOW()),
(4, 1, 'system', 'Order status changed to Dispatched', 1, NOW());
-- --------------------------------------------------------
INSERT INTO `lc_orders_items` (`id`, `order_id`, `product_id`, `option_stock_combination`, `options`, `name`, `sku`, `quantity`, `price`, `tax`, `weight`, `weight_class`) VALUES
(1, 1, 1, '2-6', 'a:1:{s:4:"Size";s:5:"Small";}', 'Yellow Duck', 'RD001-S', '1.0000', '8', '0', '1.0000', 'kg');
-- --------------------------------------------------------
INSERT INTO `lc_orders_totals` (`id`, `order_id`, `module_id`, `title`, `value`, `tax`, `calculate`, `priority`) VALUES
(1, 1, 'ot_subtotal', 'Subtotal', 8, 0, 0, 1),
(2, 1, 'ot_discount', 'Discount', -0.8, 0, 1, 2);
-- --------------------------------------------------------
INSERT INTO `lc_pages` (`id`, `status`, `dock`, `priority`, `date_updated`, `date_created`) VALUES
(1, 1, 'information', 0, NOW(), NOW()),
(2, 1, 'customer_service,information', 0, NOW(), NOW()),
(3, 1, 'customer_service,information', 0, NOW(), NOW()),
(4, 1, 'customer_service,information', 0, NOW(), NOW()),
(5, 1, 'customer_service,information', 0, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_pages_info` (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`) VALUES
(1, 1, 'en', 'About Us', '<h1>About Us</h1><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer fermentum quam eget molestie lacinia. Suspendisse consectetur velit vitae tellus commodo pharetra. Curabitur lobortis turpis tortor, id blandit metus pellentesque sit amet. Etiam cursus dolor purus, sit amet vestibulum ipsum aliquet nec. Nunc sed aliquet eros. Sed at vehicula urna. Aliquam euismod nisl a felis adipiscing tincidunt. Etiam vestibulum arcu sed massa ornare, vitae venenatis odio convallis.\r\n</p>\r\n \r\n<h2>\r\n	Subheading 2\r\n</h2>\r\n \r\n<p>\r\n	 Aliquam eget suscipit urna. Fusce sed lorem enim. Praesent dictum sagittis tellus, vel imperdiet urna tristique eu. Morbi sed orci eu odio varius tempor consequat ut lectus. Aliquam sagittis sapien vitae nulla porta adipiscing. Nullam pulvinar interdum malesuada. Ut blandit ligula quam, id luctus risus ultrices eget. Donec mattis turpis vel purus hendrerit, id ornare dui viverra. Donec at aliquet purus. Maecenas ut commodo lorem. Vivamus ornare sem eu convallis ullamcorper. \r\n</p>\r\n \r\n<h3>\r\n	Subheading 3\r\n</h3>\r\n \r\n<p>\r\n	 In in massa accumsan augue accumsan facilisis non eget dui. Ut volutpat nisl urna, ac dapibus ipsum fermentum iaculis. Donec sed lorem metus. Donec gravida et risus et consectetur. Proin aliquet, ipsum in faucibus condimentum, orci sapien sollicitudin mi, vitae molestie nunc odio vitae libero. Nullam pretium velit in sem sagittis, et facilisis mi fermentum. Aenean varius sed est et tincidunt. Praesent non imperdiet ligula. \r\n</p>', '', ''),
(2, 2, 'en', 'Cookie Policy', '<h1>Cookie Policy</h1><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer fermentum quam eget molestie lacinia. Suspendisse consectetur velit vitae tellus commodo pharetra. Curabitur lobortis turpis tortor, id blandit metus pellentesque sit amet. Etiam cursus dolor purus, sit amet vestibulum ipsum aliquet nec. Nunc sed aliquet eros. Sed at vehicula urna. Aliquam euismod nisl a felis adipiscing tincidunt. Etiam vestibulum arcu sed massa ornare, vitae venenatis odio convallis.\r\n</p>\r\n \r\n<h2>\r\n	 Subheading 2 \r\n</h2>\r\n \r\n<p>\r\n	 Aliquam eget suscipit urna. Fusce sed lorem enim. Praesent dictum sagittis tellus, vel imperdiet urna tristique eu. Morbi sed orci eu odio varius tempor consequat ut lectus. Aliquam sagittis sapien vitae nulla porta adipiscing. Nullam pulvinar interdum malesuada. Ut blandit ligula quam, id luctus risus ultrices eget. Donec mattis turpis vel purus hendrerit, id ornare dui viverra. Donec at aliquet purus. Maecenas ut commodo lorem. Vivamus ornare sem eu convallis ullamcorper. \r\n</p>\r\n \r\n<h3>\r\n	 Subheading 3 \r\n</h3>\r\n \r\n<p>\r\n	 In in massa accumsan augue accumsan facilisis non eget dui. Ut volutpat nisl urna, ac dapibus ipsum fermentum iaculis. Donec sed lorem metus. Donec gravida et risus et consectetur. Proin aliquet, ipsum in faucibus condimentum, orci sapien sollicitudin mi, vitae molestie nunc odio vitae libero. Nullam pretium velit in sem sagittis, et facilisis mi fermentum. Aenean varius sed est et tincidunt. Praesent non imperdiet ligula. \r\n</p>', '', ''),
(3, 3, 'en', 'Privacy Policy', '<h1>Privacy Policy</h1><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer fermentum quam eget molestie lacinia. Suspendisse consectetur velit vitae tellus commodo pharetra. Curabitur lobortis turpis tortor, id blandit metus pellentesque sit amet. Etiam cursus dolor purus, sit amet vestibulum ipsum aliquet nec. Nunc sed aliquet eros. Sed at vehicula urna. Aliquam euismod nisl a felis adipiscing tincidunt. Etiam vestibulum arcu sed massa ornare, vitae venenatis odio convallis.\r\n</p>\r\n \r\n<h2>\r\n	 Subheading 2 \r\n</h2>\r\n \r\n<p>\r\n	 Aliquam eget suscipit urna. Fusce sed lorem enim. Praesent dictum sagittis tellus, vel imperdiet urna tristique eu. Morbi sed orci eu odio varius tempor consequat ut lectus. Aliquam sagittis sapien vitae nulla porta adipiscing. Nullam pulvinar interdum malesuada. Ut blandit ligula quam, id luctus risus ultrices eget. Donec mattis turpis vel purus hendrerit, id ornare dui viverra. Donec at aliquet purus. Maecenas ut commodo lorem. Vivamus ornare sem eu convallis ullamcorper. \r\n</p>\r\n \r\n<h3>\r\n	 Subheading 3 \r\n</h3>\r\n \r\n<p>\r\n	 In in massa accumsan augue accumsan facilisis non eget dui. Ut volutpat nisl urna, ac dapibus ipsum fermentum iaculis. Donec sed lorem metus. Donec gravida et risus et consectetur. Proin aliquet, ipsum in faucibus condimentum, orci sapien sollicitudin mi, vitae molestie nunc odio vitae libero. Nullam pretium velit in sem sagittis, et facilisis mi fermentum. Aenean varius sed est et tincidunt. Praesent non imperdiet ligula. \r\n</p>', '', ''),
(4, 4, 'en', 'Terms of Purchase', '<h1>Terms of Purchase</h1><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer fermentum quam eget molestie lacinia. Suspendisse consectetur velit vitae tellus commodo pharetra. Curabitur lobortis turpis tortor, id blandit metus pellentesque sit amet. Etiam cursus dolor purus, sit amet vestibulum ipsum aliquet nec. Nunc sed aliquet eros. Sed at vehicula urna. Aliquam euismod nisl a felis adipiscing tincidunt. Etiam vestibulum arcu sed massa ornare, vitae venenatis odio convallis.\r\n</p>\r\n \r\n<h2>\r\n	 Subheading 2 \r\n</h2>\r\n \r\n<p>\r\n	 Aliquam eget suscipit urna. Fusce sed lorem enim. Praesent dictum sagittis tellus, vel imperdiet urna tristique eu. Morbi sed orci eu odio varius tempor consequat ut lectus. Aliquam sagittis sapien vitae nulla porta adipiscing. Nullam pulvinar interdum malesuada. Ut blandit ligula quam, id luctus risus ultrices eget. Donec mattis turpis vel purus hendrerit, id ornare dui viverra. Donec at aliquet purus. Maecenas ut commodo lorem. Vivamus ornare sem eu convallis ullamcorper. \r\n</p>\r\n \r\n<h3>\r\n	 Subheading 3 \r\n</h3>\r\n \r\n<p>\r\n	 In in massa accumsan augue accumsan facilisis non eget dui. Ut volutpat nisl urna, ac dapibus ipsum fermentum iaculis. Donec sed lorem metus. Donec gravida et risus et consectetur. Proin aliquet, ipsum in faucibus condimentum, orci sapien sollicitudin mi, vitae molestie nunc odio vitae libero. Nullam pretium velit in sem sagittis, et facilisis mi fermentum. Aenean varius sed est et tincidunt. Praesent non imperdiet ligula. \r\n</p>', '', ''),
(5, 5, 'en', 'Delivery Information', '<h1>Delivery Information</h1><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer fermentum quam eget molestie lacinia. Suspendisse consectetur velit vitae tellus commodo pharetra. Curabitur lobortis turpis tortor, id blandit metus pellentesque sit amet. Etiam cursus dolor purus, sit amet vestibulum ipsum aliquet nec. Nunc sed aliquet eros. Sed at vehicula urna. Aliquam euismod nisl a felis adipiscing tincidunt. Etiam vestibulum arcu sed massa ornare, vitae venenatis odio convallis.\r\n</p>\r\n \r\n<h2>\r\n	 Subheading 2 \r\n</h2>\r\n \r\n<p>\r\n	 Aliquam eget suscipit urna. Fusce sed lorem enim. Praesent dictum sagittis tellus, vel imperdiet urna tristique eu. Morbi sed orci eu odio varius tempor consequat ut lectus. Aliquam sagittis sapien vitae nulla porta adipiscing. Nullam pulvinar interdum malesuada. Ut blandit ligula quam, id luctus risus ultrices eget. Donec mattis turpis vel purus hendrerit, id ornare dui viverra. Donec at aliquet purus. Maecenas ut commodo lorem. Vivamus ornare sem eu convallis ullamcorper. \r\n</p>\r\n \r\n<h3>\r\n	 Subheading 3 \r\n</h3>\r\n \r\n<p>\r\n	 In in massa accumsan augue accumsan facilisis non eget dui. Ut volutpat nisl urna, ac dapibus ipsum fermentum iaculis. Donec sed lorem metus. Donec gravida et risus et consectetur. Proin aliquet, ipsum in faucibus condimentum, orci sapien sollicitudin mi, vitae molestie nunc odio vitae libero. Nullam pretium velit in sem sagittis, et facilisis mi fermentum. Aenean varius sed est et tincidunt. Praesent non imperdiet ligula. \r\n</p>', '', '');
-- --------------------------------------------------------
INSERT INTO `lc_products` (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `default_category_id`, `keywords`, `code`, `sku`, `mpn`, `gtin`, `taric`, `quantity`, `quantity_unit_id`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `tax_class_id`, `image`, `views`, `purchases`, `date_valid_from`, `date_valid_to`, `date_updated`, `date_created`) VALUES
(1, 1, 1, 0, 1, 2, 2, '', 'rd001', 'RD001', '12340001', '', '', 30, 1, 1.0000, 'kg', 6.0000, 10.0000, 10.0000, 'cm', 10.0000, 1, 'products/1-yellow-duck-1.jpg', 1, 0, '0000-00-00', '0000-00-00', NOW(), NOW()),
(2, 1, 1, 0, 1, 2, 2, '', 'rd002', 'RD002', '12340002', '', '', 30, 1, 1.0000, 'kg', 6.0000, 10.0000, 10.0000, 'cm', 10.0000, 1, 'products/2-green-duck-1.jpg', 1, 0, '0000-00-00', '0000-00-00', NOW(), NOW()),
(3, 1, 1, 0, 1, 2, 1, '', 'rd003', 'RD003', '12340003', '', '', 30, 1, 1.0000, 'kg', 6.0000, 10.0000, 10.0000, 'cm', 10.0000, 1, 'products/3-red-duck-1.jpg', 1, 0, '0000-00-00', '0000-00-00', NOW(), NOW()),
(4, 1, 1, 0, 1, 2, 1, '', 'rd004', 'RD004', '12340004', '', '', 30, 1, 1.0000, 'kg', 6.0000, 10.0000, 10.0000, 'cm', 10.0000, 1, 'products/4-blue-duck-1.jpg', 1, 0, '0000-00-00', '0000-00-00', NOW(), NOW()),
(5, 1, 1, 0, 1, 2, 1, '', 'rd005', 'RD005', '12340005', '', '', 30, 1, 1.0000, 'kg', 6.0000, 10.0000, 10.0000, 'cm', 10.0000, 1, 'products/5-purple-duck-1.jpg', 1, 0, '0000-00-00', '0000-00-00', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_products_attributes` (`product_id`, `group_id`, `value_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5);
-- --------------------------------------------------------
INSERT INTO `lc_products_to_categories` (`product_id`, `category_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(4, 1),
(5, 1);
-- --------------------------------------------------------
INSERT INTO `lc_products_campaigns` (`id`, `product_id`, `start_date`, `end_date`, `USD`) VALUES
(1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 18.0000);
-- --------------------------------------------------------
INSERT INTO `lc_products_images` (`id`, `product_id`, `filename`, `priority`) VALUES
(1, 1, 'products/1-yellow-duck-1.jpg', 1),
(2, 2, 'products/2-green-duck-1.jpg', 1),
(3, 3, 'products/3-red-duck-1.jpg', 1),
(4, 4, 'products/4-blue-duck-1.jpg', 1),
(5, 5, 'products/5-purple-duck-1.jpg', 1);
-- --------------------------------------------------------
INSERT INTO `lc_products_info` (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `head_title`, `meta_description`, `technical_data`) VALUES
(1, 1, 'en', 'Yellow Duck', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue.', '<p>\r\n	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue. Cras scelerisque dui non consequat sollicitudin. Sed pretium tortor ac auctor molestie. Nulla facilisi. Maecenas pulvinar nibh vitae lectus vehicula semper. Donec et aliquet velit. Curabitur non ullamcorper mauris. In hac habitasse platea dictumst. Phasellus ut pretium justo, sit amet bibendum urna. Maecenas sit amet arcu pulvinar, facilisis quam at, viverra nisi. Morbi sit amet adipiscing ante. Integer imperdiet volutpat ante, sed venenatis urna volutpat a. Proin justo massa, convallis vitae consectetur sit amet, facilisis id libero. \r\n</p>', '', '', 'Colors\r\nBody: Yellow\r\nEyes: Black\r\nBeak: Orange\r\n\r\nOther\r\nMaterial: Plastic'),
(2, 2, 'en', 'Green Duck', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue.', '<p>\r\n	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue. Cras scelerisque dui non consequat sollicitudin. Sed pretium tortor ac auctor molestie. Nulla facilisi. Maecenas pulvinar nibh vitae lectus vehicula semper. Donec et aliquet velit. Curabitur non ullamcorper mauris. In hac habitasse platea dictumst. Phasellus ut pretium justo, sit amet bibendum urna. Maecenas sit amet arcu pulvinar, facilisis quam at, viverra nisi. Morbi sit amet adipiscing ante. Integer imperdiet volutpat ante, sed venenatis urna volutpat a. Proin justo massa, convallis vitae consectetur sit amet, facilisis id libero. \r\n</p>', '', '', 'Colors\r\nBody: Green\r\nEyes: Black\r\nBeak: Orange\r\n\r\nOther\r\nMaterial: Plastic'),
(3, 3, 'en', 'Red Duck', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue.', '<p>\r\n	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue. Cras scelerisque dui non consequat sollicitudin. Sed pretium tortor ac auctor molestie. Nulla facilisi. Maecenas pulvinar nibh vitae lectus vehicula semper. Donec et aliquet velit. Curabitur non ullamcorper mauris. In hac habitasse platea dictumst. Phasellus ut pretium justo, sit amet bibendum urna. Maecenas sit amet arcu pulvinar, facilisis quam at, viverra nisi. Morbi sit amet adipiscing ante. Integer imperdiet volutpat ante, sed venenatis urna volutpat a. Proin justo massa, convallis vitae consectetur sit amet, facilisis id libero. \r\n</p>', '', '', 'Colors\r\nBody: Red\r\nEyes: Black\r\nBeak: Orange\r\n\r\nOther\r\nMaterial: Plastic'),
(4, 4, 'en', 'Blue Duck', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue.', '<p>\r\n	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue. Cras scelerisque dui non consequat sollicitudin. Sed pretium tortor ac auctor molestie. Nulla facilisi. Maecenas pulvinar nibh vitae lectus vehicula semper. Donec et aliquet velit. Curabitur non ullamcorper mauris. In hac habitasse platea dictumst. Phasellus ut pretium justo, sit amet bibendum urna. Maecenas sit amet arcu pulvinar, facilisis quam at, viverra nisi. Morbi sit amet adipiscing ante. Integer imperdiet volutpat ante, sed venenatis urna volutpat a. Proin justo massa, convallis vitae consectetur sit amet, facilisis id libero. \r\n</p>', '', '', 'Colors\r\nBody: Blue\r\nEyes: Black\r\nBeak: Orange\r\n\r\nOther\r\nMaterial: Plastic'),
(5, 5, 'en', 'Purple Duck', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue.', '<p>\r\n	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue. Cras scelerisque dui non consequat sollicitudin. Sed pretium tortor ac auctor molestie. Nulla facilisi. Maecenas pulvinar nibh vitae lectus vehicula semper. Donec et aliquet velit. Curabitur non ullamcorper mauris. In hac habitasse platea dictumst. Phasellus ut pretium justo, sit amet bibendum urna. Maecenas sit amet arcu pulvinar, facilisis quam at, viverra nisi. Morbi sit amet adipiscing ante. Integer imperdiet volutpat ante, sed venenatis urna volutpat a. Proin justo massa, convallis vitae consectetur sit amet, facilisis id libero. \r\n</p>', '', '', 'Colors\r\nBody: Purple\r\nEyes: Black\r\nBeak: Orange\r\n\r\nOther\r\nMaterial: Plastic');
-- --------------------------------------------------------
INSERT INTO `lc_products_options` (`id`, `product_id`, `group_id`, `function`, `sort`) VALUES
(1, 1, 1, 'select', 'priority');
-- --------------------------------------------------------
INSERT INTO `lc_products_options_values` (`id`, `product_id`, `group_id`, `value_id`, `price_operator`, `USD`, `priority`) VALUES
(1, 1, 1, 1, '+', 0.0000, 1),
(2, 1, 1, 2, '+', 2.5000, 2),
(3, 1, 1, 3, '+', 5.0000, 3);
-- --------------------------------------------------------
INSERT INTO `lc_products_options_stock` (`id`, `product_id`, `combination`, `sku`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) VALUES
(1, 1, '1-1', 'RD001-S', 1.0, 'kg', 6.0000, 10.0000, 10.0000, 'cm', 10, 0, NOW(), NOW()),
(2, 1, '1-2', 'RD001-M', 1.1, 'kg', 8.0000, 12.5000, 12.5000, 'cm', 10, 1, NOW(), NOW()),
(3, 1, '1-3', 'RD001-L', 1.2, 'kg', 10.0000, 15.0000, 15.0000, 'cm', 10, 2, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_products_prices` (`id`, `product_id`, `USD`) VALUES
(1, 1, 20.0000),
(2, 2, 20.0000),
(3, 3, 20.0000),
(4, 4, 20.0000),
(5, 5, 20.0000);
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 2 WHERE `key` = 'cookie_policy';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 3 WHERE `key` = 'privacy_policy';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 4 WHERE `key` = 'terms_of_purchase';