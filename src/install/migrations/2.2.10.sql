ALTER TABLE `lc_orders`
CHANGE `shipping_option_id` `shipping_option_id` VARCHAR(64) NOT NULL DEFAULT '',
CHANGE `payment_option_id` `payment_option_id` VARCHAR(64) NOT NULL DEFAULT '';