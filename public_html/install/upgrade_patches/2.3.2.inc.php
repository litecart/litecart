<?php

  if (!database::num_rows(database::query("SHOW COLUMNS FROM ". DB_TABLE_PREFIX ."customers LIKE 'login_attempts';"))) {
    database::query(
      "ALTER TABLE ". DB_TABLE_PREFIX ."customers
      ADD COLUMN `login_attempts` INT NOT NULL DEFAULT '0' AFTER `password_reset_token`;"
    );
  }

  if (!database::num_rows(database::query("SHOW COLUMNS FROM ". DB_TABLE_PREFIX ."customers LIKE 'date_blocked_until';"))) {
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

  if (!database::num_rows(database::query("SHOW COLUMNS FROM ". DB_TABLE_PREFIX ."customers LIKE 'date_expire_sessions';"))) {
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
