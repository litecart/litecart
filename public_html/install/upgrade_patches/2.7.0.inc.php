<?php

// Backfill lc_products_prices_history with one row per product and per campaign,
// containing all configured currencies as a JSON object in the `price` column.
// Runs after the 2.7.0.sql patch has created the table.

  $currency_codes = database::query(
    "select code from ". DB_TABLE_PREFIX ."currencies
    where status = 1
    order by priority;"
  )->fetch_all('code');

// Backfill regular prices
  $regular_select = [];
  foreach ($currency_codes as $currency_code) {
    $regular_select[] = "'". database::input($currency_code) ."'";
  }
  $regular_select = implode(', ', $regular_select);

  $regular_values = [];
  foreach ($currency_codes as $currency_code) {
    $regular_values[] = "ifnull(`". database::input($currency_code) ."`, 0)";
  }
  $regular_values = implode(', ', $regular_values);

  database::query(
    "insert into ". DB_TABLE_PREFIX ."products_prices_history
    (product_id, campaign_id, price, valid_from, valid_to)
    select product_id, 0, json_object($regular_select, $regular_values), now(), null
    from ". DB_TABLE_PREFIX ."products_prices;"
  );

// Backfill campaign prices
  $campaign_select = [];
  foreach ($currency_codes as $currency_code) {
    $campaign_select[] = "'". database::input($currency_code) ."'";
  }
  $campaign_select = implode(', ', $campaign_select);

  $campaign_values = [];
  foreach ($currency_codes as $currency_code) {
    $campaign_values[] = "ifnull(`". database::input($currency_code) ."`, 0)";
  }
  $campaign_values = implode(', ', $campaign_values);

  database::query(
    "insert into ". DB_TABLE_PREFIX ."products_prices_history
    (product_id, campaign_id, price, valid_from, valid_to)
    select product_id, id, json_object($campaign_select, $campaign_values), now(), null
    from ". DB_TABLE_PREFIX ."products_campaigns;"
  );
