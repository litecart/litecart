<?php

// Delete old files
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jquery/jquery-3.2.1.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_LOGS . 'http_request_last.log',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

// Modify some files
  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_CATEGORIES',                        '`". DB_DATABASE ."`.`". DB_TABLE_PREFIX . "categories`');" . PHP_EOL,
      'replace' => "  define('DB_TABLE_CATEGORIES',                        '`". DB_DATABASE ."`.`". DB_TABLE_PREFIX . "categories`');" . PHP_EOL
                 . "  define('DB_TABLE_CATEGORIES_IMAGES',                 '`". DB_DATABASE ."`.`". DB_TABLE_PREFIX . "categories_images`');" . PHP_EOL,
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => '<FilesMatch "\.(gif|ico|jpg|jpeg|js|pdf|png|svg|ttf)$">',
      'replace' => '<FilesMatch "\.(eot|gif|ico|jpg|jpeg|js|otf|pdf|png|svg|ttf|woff|woff2)$">',
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('WS_DIR_AJAX',        WS_DIR_HTTP_HOME . 'ajax/');\r\n",
      'replace' => '',
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }

// Complete Order Items
  $order_items_query = database::query(
    "select * from ". DB_TABLE_ORDERS_ITEMS .";"
  );

  while($order_item = database::fetch($order_items_query)) {
    if (empty($order_item['product_id'])) continue;

  // Get stock option
    if (!empty($order_item['option_stock_combination'])) {
      $stock_options_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
        where combination = '". database::input($order_item['option_stock_combination']) ."'
        limit 1;"
      );
    }

    if (!$stock_option = database::fetch($stock_options_query)) {
      $stock_options_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
        where sku = '". database::input($order_item['sku']) ."'
        limit 1;"
      );
    }

    $stock_option = database::fetch($stock_options_query);

  // Product
    $products_query = database::query(
      "select * from ". DB_TABLE_PRODUCTS ."
      where id = ". (!empty($stock_option['product_id']) ? $stock_option['product_id'] : (int)$order_item['product_id']) ."
      limit 1;"
    );

    if (!$product = database::fetch($products_query)) {
      $products_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS ."
        where sku = '". database::input($order_item['sku']) ."'
        limit 1;"
      );
    }

    if (empty($product)) continue;

  // Update order item
    database::query(
      "update ". DB_TABLE_ORDERS_ITEMS ."
      set
        gtin = '". database::input($product['gtin']) ."',
        taric = '". database::input($product['taric']) ."',
        weight = ". (!empty($stock_option['dim_x']) ? (float)$stock_option['weight'] : (float)$product['weight']) .",
        weight_class = '". database::input(!empty($stock_option['weight']) ? $stock_option['weight_class'] : $product['weight_class']) ."',
        dim_x = ". (!empty($stock_option['dim_x']) ? (float)$stock_option['dim_x'] : (float)$product['dim_x']) .",
        dim_y = ". (!empty($stock_option['dim_x']) ? (float)$stock_option['dim_y'] : (float)$product['dim_y']) .",
        dim_z = ". (!empty($stock_option['dim_x']) ? (float)$stock_option['dim_z'] : (float)$product['dim_z']) .",
        dim_class = '". database::input(!empty($stock_option['dim_x']) ? $stock_option['dim_class'] : $product['dim_class']) ."',
      where id = ". (int)$order_item['id'] ."
      limit 1;"
    );
  }

// Order Public Key
  database::query(
    "ALTER TABLE `lc_orders`
	  ADD COLUMN `public_key` VARCHAR(32) NOT NULL AFTER `domain`;"
  );

  $orders_query = database::query(
    "select * from ". DB_TABLE_ORDERS .";"
  );

  while ($order = database::fetch($orders_query)) {

    $public_key = md5($order['id'] . $order['uid'] . $order['customer_email'] . $order['date_created']);

    database::query(
      "update ". DB_TABLE_ORDERS ."
      set public_key = '". database::input($public_key) ."'
      where id = ". (int)$order['id'] .";"
    );
  }
