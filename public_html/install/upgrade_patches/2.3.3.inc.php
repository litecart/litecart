<?php

  if (!database::num_rows(database::query("SHOW COLUMNS FROM ". DB_TABLE_PREFIX ."customers LIKE 'num_logins';"))) {
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
