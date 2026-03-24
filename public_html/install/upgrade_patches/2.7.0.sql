-- Section 1: Convert all tables from MyISAM to InnoDB

alter table `lc_attribute_groups` engine=InnoDB;
-- -----
alter table `lc_attribute_groups_info` engine=InnoDB;
-- -----
alter table `lc_attribute_values` engine=InnoDB;
-- -----
alter table `lc_attribute_values_info` engine=InnoDB;
-- -----
alter table `lc_cart_items` engine=InnoDB;
-- -----
alter table `lc_categories` engine=InnoDB;
-- -----
alter table `lc_categories_filters` engine=InnoDB;
-- -----
alter table `lc_categories_info` engine=InnoDB;
-- -----
alter table `lc_countries` engine=InnoDB;
-- -----
alter table `lc_currencies` engine=InnoDB;
-- -----
alter table `lc_customers` engine=InnoDB;
-- -----
alter table `lc_delivery_statuses` engine=InnoDB;
-- -----
alter table `lc_delivery_statuses_info` engine=InnoDB;
-- -----
alter table `lc_emails` engine=InnoDB;
-- -----
alter table `lc_geo_zones` engine=InnoDB;
-- -----
alter table `lc_languages` engine=InnoDB;
-- -----
alter table `lc_manufacturers` engine=InnoDB;
-- -----
alter table `lc_manufacturers_info` engine=InnoDB;
-- -----
alter table `lc_modules` engine=InnoDB;
-- -----
alter table `lc_newsletter_recipients` engine=InnoDB;
-- -----
alter table `lc_orders` engine=InnoDB;
-- -----
alter table `lc_orders_comments` engine=InnoDB;
-- -----
alter table `lc_orders_items` engine=InnoDB;
-- -----
alter table `lc_order_statuses` engine=InnoDB;
-- -----
alter table `lc_order_statuses_info` engine=InnoDB;
-- -----
alter table `lc_orders_totals` engine=InnoDB;
-- -----
alter table `lc_pages` engine=InnoDB;
-- -----
alter table `lc_pages_info` engine=InnoDB;
-- -----
alter table `lc_products` engine=InnoDB;
-- -----
alter table `lc_products_attributes` engine=InnoDB;
-- -----
alter table `lc_products_campaigns` engine=InnoDB;
-- -----
alter table `lc_products_images` engine=InnoDB;
-- -----
alter table `lc_products_info` engine=InnoDB;
-- -----
alter table `lc_products_options` engine=InnoDB;
-- -----
alter table `lc_products_options_values` engine=InnoDB;
-- -----
alter table `lc_products_options_stock` engine=InnoDB;
-- -----
alter table `lc_products_prices` engine=InnoDB;
-- -----
alter table `lc_products_to_categories` engine=InnoDB;
-- -----
alter table `lc_quantity_units` engine=InnoDB;
-- -----
alter table `lc_quantity_units_info` engine=InnoDB;
-- -----
alter table `lc_settings` engine=InnoDB;
-- -----
alter table `lc_settings_groups` engine=InnoDB;
-- -----
alter table `lc_slides` engine=InnoDB;
-- -----
alter table `lc_slides_info` engine=InnoDB;
-- -----
alter table `lc_sold_out_statuses` engine=InnoDB;
-- -----
alter table `lc_sold_out_statuses_info` engine=InnoDB;
-- -----
alter table `lc_suppliers` engine=InnoDB;
-- -----
alter table `lc_tax_classes` engine=InnoDB;
-- -----
alter table `lc_tax_rates` engine=InnoDB;
-- -----
alter table `lc_translations` engine=InnoDB;
-- -----
alter table `lc_users` engine=InnoDB;
-- -----
alter table `lc_zones` engine=InnoDB;
-- -----
alter table `lc_zones_to_geo_zones` engine=InnoDB;
-- -----

-- Section 2: Convert FLOAT columns to DECIMAL

alter table `lc_cart_items`
  modify column `quantity` DECIMAL(12,4) NOT NULL DEFAULT '0';
-- -----
alter table `lc_currencies`
  modify column `value` DECIMAL(12,6) UNSIGNED NOT NULL DEFAULT '0';
-- -----
alter table `lc_orders`
  modify column `weight_total` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `currency_value` DECIMAL(12,6) UNSIGNED NOT NULL DEFAULT '0',
  modify column `payment_due` DECIMAL(12,4) NOT NULL DEFAULT '0',
  modify column `tax_total` DECIMAL(12,4) NOT NULL DEFAULT '0';
-- -----
alter table `lc_orders_items`
  modify column `quantity` DECIMAL(12,4) NOT NULL DEFAULT '0',
  modify column `price` DECIMAL(12,4) NOT NULL DEFAULT '0',
  modify column `tax` DECIMAL(12,4) NOT NULL DEFAULT '0',
  modify column `weight` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `dim_x` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `dim_y` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `dim_z` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0';
-- -----
alter table `lc_orders_totals`
  modify column `value` DECIMAL(12,4) NOT NULL DEFAULT '0',
  modify column `tax` DECIMAL(12,4) NOT NULL DEFAULT '0';
-- -----
alter table `lc_products`
  modify column `quantity` DECIMAL(12,4) NOT NULL DEFAULT '0',
  modify column `quantity_min` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0.0000',
  modify column `quantity_max` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0.0000',
  modify column `quantity_step` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0.0000',
  modify column `weight` DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `dim_x` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `dim_y` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `dim_z` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `purchase_price` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `recommended_price` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0';
-- -----
alter table `lc_products_campaigns`
  modify column `USD` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `EUR` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0';
-- -----
alter table `lc_products_options_values`
  modify column `USD` DECIMAL(12,4) NOT NULL DEFAULT '0',
  modify column `EUR` DECIMAL(12,4) NOT NULL DEFAULT '0';
-- -----
alter table `lc_products_options_stock`
  modify column `weight` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `dim_x` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `dim_y` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `dim_z` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0',
  modify column `quantity` DECIMAL(12,4) NOT NULL DEFAULT '0';
-- -----
alter table `lc_products_prices`
  modify column `USD` DECIMAL(12,4) NOT NULL DEFAULT '0',
  modify column `EUR` DECIMAL(12,4) NOT NULL DEFAULT '0';
-- -----
alter table `lc_tax_rates`
  modify column `rate` DECIMAL(10,4) NOT NULL DEFAULT '0';
-- -----

-- Section 3: Add foreign key constraints (idempotent — drop if exists before adding)

set @fk_exists = (select count(*) from information_schema.table_constraints where constraint_name = 'fk_orders_items_order' and table_schema = database());
set @sql = if(@fk_exists = 0, 'alter table `lc_orders_items` add constraint `fk_orders_items_order` foreign key (`order_id`) references `lc_orders` (`id`) on delete cascade', 'select 1');
prepare stmt from @sql;
execute stmt;
deallocate prepare stmt;
-- -----
set @fk_exists = (select count(*) from information_schema.table_constraints where constraint_name = 'fk_orders_totals_order' and table_schema = database());
set @sql = if(@fk_exists = 0, 'alter table `lc_orders_totals` add constraint `fk_orders_totals_order` foreign key (`order_id`) references `lc_orders` (`id`) on delete cascade', 'select 1');
prepare stmt from @sql;
execute stmt;
deallocate prepare stmt;
-- -----
set @fk_exists = (select count(*) from information_schema.table_constraints where constraint_name = 'fk_orders_comments_order' and table_schema = database());
set @sql = if(@fk_exists = 0, 'alter table `lc_orders_comments` add constraint `fk_orders_comments_order` foreign key (`order_id`) references `lc_orders` (`id`) on delete cascade', 'select 1');
prepare stmt from @sql;
execute stmt;
deallocate prepare stmt;
