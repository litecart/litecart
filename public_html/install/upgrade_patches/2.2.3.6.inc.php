<?php

  if (!database::num_rows(database::query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '". DB_SERVER ."' AND TABLE_NAME = '". DB_TABLE_PREFIX ."lc_attribute_groups' AND COLUMN_NAME = 'sort';"))) {
    database::query("ALTER TABLE ". DB_TABLE_PREFIX ."lc_attribute_groups ADD COLUMN `sort` ENUM('alphabetical','priority') NOT NULL DEFAULT 'alphabetical' AFTER `code`;");
  }

  if (database::num_rows(database::query("SHOW INDEX FROM ". DB_TABLE_PREFIX ."products_options_values WHERE Key_name = 'product_option_value';"))) {
    database::query("ALTER TABLE ". DB_TABLE_PREFIX ."products_options_values DROP INDEX `product_option_value`;");
  }

  database::query("ALTER TABLE ". DB_TABLE_PREFIX ."products_options_values ADD UNIQUE INDEX `product_option_value` (`product_id`, `group_id`, `value_id`, `custom_value`);");
