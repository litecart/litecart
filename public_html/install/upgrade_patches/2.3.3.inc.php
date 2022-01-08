<?php

  if (database::num_rows(database::query("SHOW COLUMNS FROM ". DB_TABLE_PREFIX ."customers LIKE 'num_logins';"))) {
    database::query(
      "ALTER TABLE ". DB_TABLE_PREFIX ."customers
      CHANGE COLUMN `num_logins` `total_logins` INT(11) NOT NULL DEFAULT '0' AFTER `login_attempts`;"
    );
  }

  if (!database::num_rows(database::query("SHOW COLUMNS FROM ". DB_TABLE_PREFIX ."newsletter_recipients LIKE 'client_ip';"))) {
    database::query(
      "ALTER TABLE ". DB_TABLE_PREFIX ."newsletter_recipients
      ADD COLUMN `client_ip` VARCHAR(64) NOT NULL DEFAULT '' AFTER `email`;"
    );
  }

// Recalculate total quantity in case of inconsistent stock options quantity from bug in 2.3.2
  $products_query = database::query(
    "SELECT product_id as id, sum(quantity) as quantity
    FROM ". DB_TABLE_PREFIX ."products_options_stock
    GROUP BY product_id;"
  );

  while ($product = database::fetch($products_query)) {
    database::query(
      "UPDATE ". DB_TABLE_PREFIX ."products
      SET quantity = ". (float)$product['quantity'] ."
      WHERE ID = ". (int)$product['id'] .";"
    );
  }
