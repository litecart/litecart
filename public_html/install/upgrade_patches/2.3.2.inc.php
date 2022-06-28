<?php

  if (!database::query("SHOW COLUMNS FROM ". DB_TABLE_PREFIX ."customers LIKE 'login_attempts';")->num_rows) {
    database::query(
      "ALTER TABLE ". DB_TABLE_PREFIX ."customers
      ADD COLUMN `login_attempts` INT NOT NULL DEFAULT '0' AFTER `password_reset_token`;"
    );
  }

  if (!database::query("SHOW COLUMNS FROM ". DB_TABLE_PREFIX ."customers LIKE 'date_blocked_until';")->num_rows) {
    database::query(
      "ALTER TABLE ". DB_TABLE_PREFIX ."customers
      ADD COLUMN `date_blocked_until` TIMESTAMP NULL AFTER `date_login`;"
    );
  } else {
    database::query(
      "ALTER TABLE ". DB_TABLE_PREFIX ."customers
      CHANGE COLUMN `date_blocked_until` `date_blocked_until` TIMESTAMP NULL AFTER `date_login`;"
    );
  }

  if (!database::query("SHOW COLUMNS FROM ". DB_TABLE_PREFIX ."customers LIKE 'date_expire_sessions';")->num_rows) {
    database::query(
      "ALTER TABLE ". DB_TABLE_PREFIX ."customers
      ADD COLUMN `date_expire_sessions` TIMESTAMP NULL AFTER `date_blocked_until`;"

    );
  } else {
    database::query(
      "ALTER TABLE ". DB_TABLE_PREFIX ."customers
      CHANGE COLUMN `date_expire_sessions` `date_expire_sessions` TIMESTAMP NULL AFTER `date_blocked_until`;"
    );
  }
