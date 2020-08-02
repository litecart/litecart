ALTER TABLE `lc_products_options_values`
DROP INDEX `product_option_value`,
ADD UNIQUE INDEX `product_option_value` (`product_id`, `group_id`, `value_id`, `custom_value`);
