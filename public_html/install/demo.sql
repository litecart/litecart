INSERT INTO `lc_categories` (`id`, `parent_id`, `status`, `code`, `keywords`, `image`, `priority`, `date_updated`, `date_created`) VALUES
(1, 0, 1, '', '', '', 0, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_categories_info` (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `head_title`, `h1_title`, `meta_description`, `meta_keywords`) VALUES
(1, 1, 'en', 'Rubber Ducks', '', '', '', '', '', '');
-- --------------------------------------------------------
INSERT INTO `lc_customers` (`id`, `email`, `password`, `tax_id`, `company`, `firstname`, `lastname`, `address1`, `address2`, `postcode`, `city`, `country_code`, `zone_code`, `phone`, `mobile`, `different_shipping_address`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_zone_code`, `newsletter`, `date_updated`, `date_created`) VALUES
(1, 'user@email.com', '000000000000000000000000000000000000000000000000', '0000000000', 'ACME Corp.', 'John', 'Doe', 'Longway Street 1', '', 'XX1 X1', 'London', 'GB', '', '1-555-123-4567', '', 0, '', '', '', '', '', '', '', '', '', 0, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_delivery_status` (`id`, `date_updated`, `date_created`) VALUES
(1, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_delivery_status_info` (`id`, `delivery_status_id`, `language_code`, `name`, `description`) VALUES
(1, 1, 'en', '3-5 days', '');
-- --------------------------------------------------------
INSERT INTO `lc_geo_zones` (`id`, `name`, `description`, `date_updated`, `date_created`) VALUES
(1, 'UK VAT Zone', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_manufacturers` (`id`, `status`, `code`, `name`, `keywords`, `image`, `date_updated`, `date_created`) VALUES
(1, 1, 'acme', 'ACME Corp.', '', 'manufacturers/1-acme-corp.jpg', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_manufacturers_info` (`id`, `manufacturer_id`, `language_code`, `short_description`, `description`, `h1_title`, `head_title`, `meta_description`, `meta_keywords`, `link`) VALUES
(1, 1, 'en', '', '', '', '', '', '', '');
-- --------------------------------------------------------
INSERT INTO `lc_option_groups` (`id`, `function`, `required`, `sort`, `date_updated`, `date_created`) VALUES
(1, 'select', 1, 'priority', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_option_groups_info` (`id`, `group_id`, `language_code`, `name`, `description`) VALUES
(1, 1, 'en', 'Size', '');
-- --------------------------------------------------------
INSERT INTO `lc_option_values` (`id`, `group_id`, `value`, `priority`) VALUES
(1, 1, '', 1),
(2, 1, '', 2),
(3, 1, '', 3);
-- --------------------------------------------------------
INSERT INTO `lc_option_values_info` (`id`, `value_id`, `language_code`, `name`) VALUES
(1, 1, 'en', 'Small'),
(2, 2, 'en', 'Medium'),
(3, 3, 'en', 'Large');
-- --------------------------------------------------------
INSERT INTO `lc_products` (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `keywords`, `code`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `tax_class_id`, `image`, `views`, `purchases`, `date_valid_from`, `date_valid_to`, `date_updated`, `date_created`) VALUES
(1, 1, 1, 0, 1, 2, '1', '', '', 'rd001', 'RD001', '', '', 30, 0.5000, 'kg', 6.0000, 10.0000, 10.0000, 'm', 5.0000, 1, 'products/1-rubber-duck-1.jpg', 1, 0, '0000-00-00', '0000-00-00', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_products_campaigns` (`id`, `product_id`, `start_date`, `end_date`, `SEK`, `EUR`) VALUES
(1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0.0000, 9.0000);
-- --------------------------------------------------------
INSERT INTO `lc_products_images` (`id`, `product_id`, `filename`, `priority`) VALUES
(1, 1, 'products/1-rubber-duck-1.jpg', 1);
-- --------------------------------------------------------
INSERT INTO `lc_products_info` (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) VALUES
(1, 1, 'en', 'Rubber Duck', '', '<p>\r\n	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sollicitudin ante massa, eget ornare libero porta congue. Cras scelerisque dui non consequat sollicitudin. Sed pretium tortor ac auctor molestie. Nulla facilisi. Maecenas pulvinar nibh vitae lectus vehicula semper. Donec et aliquet velit. Curabitur non ullamcorper mauris. In hac habitasse platea dictumst. Phasellus ut pretium justo, sit amet bibendum urna. Maecenas sit amet arcu pulvinar, facilisis quam at, viverra nisi. Morbi sit amet adipiscing ante. Integer imperdiet volutpat ante, sed venenatis urna volutpat a. Proin justo massa, convallis vitae consectetur sit amet, facilisis id libero. \r\n</p>', '', '', '', 'Color: Yellow\r\nOther\r\nMaterial: Plastic');
-- --------------------------------------------------------
INSERT INTO `lc_products_options` (`id`, `product_id`, `group_id`, `value_id`, `price_operator`, `EUR`, `SEK`, `priority`, `date_updated`, `date_created`) VALUES
(1, 1, 1, 1, '+', 0.0000, 0.0000, 1, NOW(), NOW()),
(2, 1, 1, 2, '+', 12.5000, 0.0000, 2, NOW(), NOW()),
(3, 1, 1, 3, '+', 15.0000, 0.0000, 3, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_products_options_stock` (`id`, `product_id`, `combination`, `sku`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) VALUES
(1, 1, '1-1', 'RD001-S', 1, 'kg', 6.0000, 10.0000, 10.0000, 'cm', 10, 0, NOW(), NOW()),
(2, 1, '1-2', 'RD001-M', 1, 'kg', 8.0000, 12.5000, 12.5000, 'cm', 10, 1, NOW(), NOW()),
(3, 1, '1-3', 'RD001-L', 1, 'kg', 10.0000, 15.0000, 15.0000, 'cm', 10, 2, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_products_prices` (`id`, `product_id`, `EUR`) VALUES
(1, 1, 10.0000);
-- --------------------------------------------------------
INSERT INTO `lc_product_groups` (`id`, `status`, `date_updated`, `date_created`) VALUES
(1, 0, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_product_groups_info` (`id`, `product_group_id`, `language_code`, `name`) VALUES
(1, 1, 'en', 'Gender');
-- --------------------------------------------------------
INSERT INTO `lc_product_groups_values` (`id`, `product_group_id`, `date_updated`, `date_created`) VALUES
(1, 1, NOW(), NOW()),
(2, 1, NOW(), NOW()),
(3, 1, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_product_groups_values_info` (`id`, `product_group_value_id`, `language_code`, `name`) VALUES
(1, 1, 'en', 'Male'),
(2, 2, 'en', 'Female'),
(3, 3, 'en', 'Unisex');
-- --------------------------------------------------------
INSERT INTO `lc_slides` (`id`, `status`, `language_code`, `name`, `caption`, `link`, `image`, `priority`, `date_valid_from`, `date_valid_to`, `date_updated`, `date_created`) VALUES
(1, 1, 'en', 'Slide 1', '', '', 'slides/1-nemo.jpg', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NOW(), NOW()),
(2, 1, 'en', 'Slide 2', 'A slide with a caption and a link.', 'http://www.litecart.net', 'slides/2-walle.jpg', 2, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_sold_out_status` (`id`, `orderable`, `date_updated`, `date_created`) VALUES
(1, 1, NOW(), NOW()),
(2, 1, NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_sold_out_status_info` (`id`, `sold_out_status_id`, `language_code`, `name`, `description`) VALUES
(1, 1, 'en', 'Sold out', ''),
(2, 2, 'en', 'Temporary sold out', '');
-- --------------------------------------------------------
INSERT INTO `lc_tax_classes` (`id`, `name`, `description`, `date_updated`, `date_created`) VALUES
(1, 'Standard', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_rates` (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) VALUES
(1, 1, 1, 'percent', 'UK VAT 20%', '', 20.0000, 'both', 'both', NOW(), NOW());