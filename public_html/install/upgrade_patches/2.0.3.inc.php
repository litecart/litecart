<?php

// Copy missing short description from description
  $products_info_query = database::query(
    "select id, description from ". DB_TABLE_PRODUCTS_INFO ."
    where short_description = ''
    and description != '';"
  );

  while($product_info = database::fetch($products_query)) {

    $short_description = strip_tags($product_info['description']);
    $short_description = preg_replace('#\R#s', ' ', $short_description);
    $short_description = preg_replace('#\s+#s', ' ', $short_description);

    if (strlen($short_description) > 250) {
      $short_description = substr($short_description, 0, strpos(wordwrap($short_description, 250), "\n")) . ' …';
    }

    database::query(
      "update ". DB_TABLE_PRODUCTS_INFO ."
      set short_description = '". database::input($short_description) ."'
      where id = ". (int)$product_info['id'] ."
      limit 1;"
    );
  }
