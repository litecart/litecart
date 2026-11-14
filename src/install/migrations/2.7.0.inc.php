<?php

// Backfill lc_products_prices_history with one row per product and per campaign,
// containing all configured currencies as a JSON object in the `price` column.
// Runs after the 2.7.0.sql patch has created the table.

  $currency_codes = database::query(
    "select code from ". DB_PREFIX ."currencies
    where status = 1
    order by priority;"
  )->fetch_all('code');

// Backfill regular prices
  if (!empty($currency_codes)) {
    $regular_query = database::query(
      "select product_id, `". implode('`, `', array_map('database::input', $currency_codes)) ."`
      from ". DB_PREFIX ."products_prices;"
    );

    while ($row = database::fetch($regular_query)) {
      $price = [];
      foreach ($currency_codes as $currency_code) {
        $price[$currency_code] = isset($row[$currency_code]) ? (float)$row[$currency_code] : 0;
      }

      database::insert('products_prices_history', [
        'product_id' => (int)$row['product_id'],
        'campaign_id' => 0,
        'price' => json_encode($price, JSON_UNESCAPED_UNICODE),
        'valid_from' => date('Y-m-d H:i:s'),
        'valid_to' => null,
      ]);
    }
  }

// Backfill campaign prices
  if (!empty($currency_codes)) {
    $campaign_query = database::query(
      "select id, product_id, `". implode('`, `', array_map('database::input', $currency_codes)) ."`
      from ". DB_PREFIX ."products_campaigns;"
    );

    while ($row = database::fetch($campaign_query)) {
      $price = [];
      foreach ($currency_codes as $currency_code) {
        $price[$currency_code] = isset($row[$currency_code]) ? (float)$row[$currency_code] : 0;
      }

      database::insert('products_prices_history', [
        'product_id' => (int)$row['product_id'],
        'campaign_id' => (int)$row['id'],
        'price' => json_encode($price, JSON_UNESCAPED_UNICODE),
        'valid_from' => date('Y-m-d H:i:s'),
        'valid_to' => null,
      ]);
    }
  }