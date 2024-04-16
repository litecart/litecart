<?php

  perform_action('delete', [
    FS_DIR_ADMIN . 'orders.app/add_custom_item.inc.php',
    FS_DIR_ADMIN . 'orders.app/get_address.json.inc.php',
    FS_DIR_STORAGE . 'data/bad_urls.txt',
    FS_DIR_STORAGE . 'data/blacklist.txt',
    FS_DIR_STORAGE . 'data/whitelist.txt',
    FS_DIR_APP . 'ext/jquery/jquery-3.3.1.min.js',
    FS_DIR_APP . 'ext/fontawesome/css',
    FS_DIR_APP . 'ext/fontawesome/fonts',
    FS_DIR_APP . 'includes/classes/email.inc.php',
    FS_DIR_APP . 'includes/classes/http_client.inc.php',
    FS_DIR_APP . 'includes/classes/index.html',
    FS_DIR_APP . 'includes/classes/module.inc.php',
    FS_DIR_APP . 'includes/classes/smtp.inc.php',
    FS_DIR_APP . 'includes/classes/system.inc.php',
    FS_DIR_APP . 'includes/classes/view.inc.php',
    FS_DIR_APP . 'includes/classes/vmod.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_attribute_group.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_category.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_country.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_currency.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_customer.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_delivery_status.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_email.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_geo_zone.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_image.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_language.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_manufacturer.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_module.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_option_group.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_order.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_order_status.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_page.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_product.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_product_group.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_quantity_unit.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_slide.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_sold_out_status.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_supplier.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_tax_class.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_tax_rate.inc.php',
    FS_DIR_APP . 'includes/controllers/ctrl_user.inc.php',
    FS_DIR_APP . 'includes/controllers/index.html',
    FS_DIR_APP . 'includes/functions/func_email.inc.php',
    FS_DIR_APP . 'includes/functions/func_http.inc.php',
    FS_DIR_APP . 'includes/library/lib_catalog.inc.php',
    FS_DIR_APP . 'includes/library/lib_link.inc.php',
    FS_DIR_APP . 'includes/library/lib_security.inc.php',
    FS_DIR_STORAGE . 'logs/http_request_last.log',
    FS_DIR_APP . 'includes/templates/default.catalog/views/column_left.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/views/site_cookie_notice.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_cart.html.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_customer.html.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_payment.html.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_shipping.html.inc.php',
    FS_DIR_APP . 'pages/ajax/checkout_summary.html.inc.php',
  ]);

  if (preg_match("#define\('WS_DIR_ADMIN',\s+WS_DIR_HTTP_HOME \. '(.*?)'\);#", file_get_contents(FS_DIR_APP . 'includes/config.inc.php'), $matches)) {
    $admin_folder_name = rtrim($matches[1], '/');
  } else {
    die('Could not extract name of admin folder');
  }

// Modify some files
  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "## Files and Directory  ##############################################",
        'replace' => "## Files and Directories #############################################",
      ],
      [
        'search'  => "    //if (is_writable(__FILE__)) chmod(__FILE__, 0444);" . PHP_EOL . PHP_EOL,
        'replace' => "",
      ],
      [
        'search'  => "  define('WS_DIR_ADMIN',       WS_DIR_HTTP_HOME . '{ADMIN_FOLDER}/');" . PHP_EOL,
        'replace' => '',
      ],
      [
        'search'  => "  define('WS_DIR_AJAX',        WS_DIR_APP . 'ajax/');" . PHP_EOL,
        'replace' => '',
      ],
      [
        'search'  => "  ini_set('error_log', FS_DIR_HTTP_ROOT . WS_DIR_LOGS . 'errors.log');",
      'replace' => "  ini_set('error_log', FS_DIR_STORAGE . 'logs/errors.log');",
      ],
    ],
    FS_DIR_APP . 'install/.htaccess' => [
      [
        'search'  => '<FilesMatch "\.(gif|ico|jpg|jpeg|js|pdf|png|svg|ttf)$">',
        'replace' => '<FilesMatch "\.(eot|gif|ico|jpg|jpeg|js|otf|pdf|png|svg|ttf|woff|woff2)$">',
      ],
    ],
  ]);

  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "  define('WS_DIR_ADMIN',       WS_DIR_HTTP_HOME . '". $admin_folder_name ."/');" . PHP_EOL,
        'replace' => "",
      ],
      [
        'search'  => "## Files and Directories #############################################" . PHP_EOL
                   . "######################################################################" . PHP_EOL,
        'replace' => "## Files and Directories #############################################" . PHP_EOL
                   . "######################################################################" . PHP_EOL
                   . PHP_EOL
                   . "  define('BACKEND_ALIAS', '". $admin_folder_name ."');" . PHP_EOL
                   . PHP_EOL
                   . "// File System" . PHP_EOL
                   . "  define('DOCUMENT_ROOT',      rtrim(str_replace(\"\\\\\", '/', realpath(\$_SERVER['DOCUMENT_ROOT'])), '/'));" . PHP_EOL
                   . PHP_EOL
                   . "  define('FS_DIR_APP',         DOCUMENT_ROOT . rtrim(str_replace(DOCUMENT_ROOT, '', str_replace(\"\\\\\", '/', realpath(__DIR__.'/..'))), '/') . '/');" . PHP_EOL
                   . "  define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL
                   . PHP_EOL
                   . "// Web System" . PHP_EOL
                   . "  define('WS_DIR_APP',         rtrim(str_replace(DOCUMENT_ROOT, '', str_replace(\"\\\\\", '/', realpath(__DIR__.'/..'))), '/') . '/');" . PHP_EOL
                   . "  define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL
                   . PHP_EOL
                   . "######################################################################" . PHP_EOL
                   . "## Backwards Compatible Directory Definitions (LiteCart <2.2)  #######" . PHP_EOL
                   . "######################################################################" . PHP_EOL
                   . PHP_EOL,
      ],
      [
        'search'  => "  define('WS_DIR_CONTROLLERS', WS_DIR_INCLUDES  . 'controllers/');" . PHP_EOL,
        'replace' => "  define('WS_DIR_CONTROLLERS', WS_DIR_INCLUDES  . 'controllers/'); // Deprecated in favour of Entities" . PHP_EOL
                   . "  define('WS_DIR_ENTITIES',    WS_DIR_INCLUDES  . 'entities/');" . PHP_EOL,
      ],
      [
        'search'  => "  define('DB_TABLE_CART_ITEMS',                        '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'cart_items`');" . PHP_EOL,
        'replace' => "  define('DB_TABLE_ATTRIBUTE_GROUPS',                  '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'attribute_groups`');" . PHP_EOL
                   . "  define('DB_TABLE_ATTRIBUTE_GROUPS_INFO',             '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'attribute_groups_info`');" . PHP_EOL
                   . "  define('DB_TABLE_ATTRIBUTE_VALUES',                  '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'attribute_values`');" . PHP_EOL
                   . "  define('DB_TABLE_ATTRIBUTE_VALUES_INFO',             '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'attribute_values_info`');" . PHP_EOL
                   . "  define('DB_TABLE_CART_ITEMS',                        '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'cart_items`');" . PHP_EOL,
      ],
      [
        'search'  => "  define('DB_TABLE_CATEGORIES',                        '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'categories`');" . PHP_EOL,
        'replace' => "  define('DB_TABLE_CATEGORIES',                        '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'categories`');" . PHP_EOL
                   . "  define('DB_TABLE_CATEGORIES_FILTERS',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'categories_filters`');" . PHP_EOL
                   . "  define('DB_TABLE_CATEGORIES_IMAGES',                 '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'categories_images`');" . PHP_EOL,
      ],
      [
        'search'  => "  define('DB_TABLE_DELIVERY_STATUSES_INFO',            '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'delivery_statuses_info`');" . PHP_EOL,
        'replace' => "  define('DB_TABLE_DELIVERY_STATUSES_INFO',            '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'delivery_statuses_info`');" . PHP_EOL
                   . "  define('DB_TABLE_EMAILS',                            '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'emails`');" . PHP_EOL,
      ],
      [
        'search'  => "  define('DB_TABLE_PRODUCT_GROUPS',                    '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'product_groups`');" . PHP_EOL,
        'replace' => "",
      ],
      [
        'search'  => "  define('DB_TABLE_PRODUCT_GROUPS_INFO',               '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'product_groups_info`');" . PHP_EOL,
        'replace' => "",
      ],
      [
        'search'  => "  define('DB_TABLE_PRODUCT_GROUPS_VALUES',             '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'product_groups_values`');" . PHP_EOL,
        'replace' => "",
      ],
      [
        'search'  => "  define('DB_TABLE_PRODUCT_GROUPS_VALUES_INFO',        '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'product_groups_values_info`');" . PHP_EOL,
        'replace' => "",
      ],
      [
        'search'  => "  define('DB_TABLE_PRODUCTS',                          '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products`');" . PHP_EOL,
        'replace' => "  define('DB_TABLE_PRODUCTS',                          '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products`');" . PHP_EOL
                   . "  define('DB_TABLE_PRODUCTS_ATTRIBUTES',               '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products_attributes`');" . PHP_EOL,
      ],
    ],
  ], 'abort');

// Complete Order Items
  $order_items_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."orders_items;"
  );

  while ($order_item = database::fetch($order_items_query)) {
    if (empty($order_item['product_id'])) continue;

  // Get stock option
    if (!empty($order_item['option_stock_combination'])) {
      $stock_option = database::query(
        "select * from ". DB_TABLE_PREFIX ."products_options_stock
        where combination = '". database::input($order_item['option_stock_combination']) ."'
        limit 1;"
      )->fetch();
    }

    if (empty($stock_option)) {
      $stock_option = database::query(
        "select * from ". DB_TABLE_PREFIX ."products_options_stock
        where sku = '". database::input($order_item['sku']) ."'
        limit 1;"
      )->fetch();
    }

  // Product
    $product = database::query(
      "select * from ". DB_TABLE_PREFIX ."products
      where id = ". (!empty($stock_option['product_id']) ? $stock_option['product_id'] : (int)$order_item['product_id']) ."
      limit 1;"
    )->fetch();

    if (!$product) {
      $products = database::query(
        "select * from ". DB_TABLE_PREFIX ."products
        where sku = '". database::input($order_item['sku']) ."'
        limit 1;"
      )->fetch();
    }

    if (empty($product)) continue;

  // Update order item
    database::query(
      "update ". DB_TABLE_PREFIX ."orders_items
      set gtin = '". database::input($product['gtin']) ."',
        taric = '". database::input($product['taric']) ."',
        weight = ". (!empty($stock_option['dim_x']) ? (float)$stock_option['weight'] : (float)$product['weight']) .",
        weight_class = '". database::input(!empty($stock_option['weight']) ? $stock_option['weight_class'] : $product['weight_class']) ."',
        dim_x = ". (!empty($stock_option['dim_x']) ? (float)$stock_option['dim_x'] : (float)$product['dim_x']) .",
        dim_y = ". (!empty($stock_option['dim_x']) ? (float)$stock_option['dim_y'] : (float)$product['dim_y']) .",
        dim_z = ". (!empty($stock_option['dim_x']) ? (float)$stock_option['dim_z'] : (float)$product['dim_z']) .",
        dim_class = '". database::input(!empty($stock_option['dim_x']) ? $stock_option['dim_class'] : $product['dim_class']) ."'
      where id = ". (int)$order_item['id'] ."
      limit 1;"
    );
  }

// Order Public Key
  database::query(
    "ALTER TABLE `". DB_TABLE_PREFIX ."orders`
    ADD COLUMN `public_key` VARCHAR(32) NOT NULL AFTER `domain`;"
  );

  $orders_query = database::query(
    "select * from ". DB_TABLE_PREFIX ."orders
    where public_key = '';"
  );

  while ($order = database::fetch($orders_query)) {

    $public_key = md5($order['id'] . $order['uid'] . $order['customer_email'] . $order['date_created']);

    database::query(
      "update ". DB_TABLE_PREFIX ."orders
      set public_key = '". database::input($public_key) ."'
      where id = ". (int)$order['id'] .";"
    );
  }

// Fix unique indexes (ALTER IGNORE is deprecated)
  $tables = [
    ['table' => DB_TABLE_PREFIX.'categories_info',            'index' => 'category',                 'columns' => '`category_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'slides_info',                'index' => 'slide_info',               'columns' => '`slide_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'delivery_statuses_info',     'index' => 'delivery_status_info',     'columns' => '`delivery_status_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'manufacturers_info',         'index' => 'manufacturer_info',        'columns' => '`manufacturer_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'option_groups_info',         'index' => 'option_group_info',        'columns' => '`group_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'option_values_info',         'index' => 'option_value_info',        'columns' => '`value_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'order_statuses_info',        'index' => 'order_status_info',        'columns' => '`order_status_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'pages_info',                 'index' => 'page_info',                'columns' => '`page_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'products_info',              'index' => 'product_info',             'columns' => '`product_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'products_options',           'index' => 'product_option',           'columns' => '`product_id`, `group_id`, `value_id`'],
    ['table' => DB_TABLE_PREFIX.'products_options_stock',     'index' => 'product_option_stock',     'columns' => '`product_id`, `combination`'],
    ['table' => DB_TABLE_PREFIX.'products_to_categories',     'index' => 'mapping',                  'columns' => '`product_id`, `category_id`'],
    ['table' => DB_TABLE_PREFIX.'quantity_units_info',        'index' => 'quantity_unit_info',       'columns' => '`quantity_unit_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'sold_out_statuses_info',     'index' => 'sold_out_status_info',     'columns' => '`sold_out_status_id`, `language_code`'],
    ['table' => DB_TABLE_PREFIX.'zones_to_geo_zones',         'index' => 'region',                   'columns' => '`geo_zone_id`, `country_code`, `zone_code`'],
  ];

  foreach ($tables as $table) {
    $index_query = database::query(
      "SHOW KEYS FROM `". $table['table'] ."`
      WHERE Key_name = '". $table['index'] ."'
      AND Non_unique = 0;"
    );

    if (!database::num_rows($index_query)) {
      database::query(
        "ALTER TABLE `". $table['table'] ."`
        ADD UNIQUE KEY `". $table['index'] ."` (". $table['columns'] .");"
      );
    }
  }

// Remove some indexes
  $index_query = database::query(
    "SHOW KEYS FROM `". DB_TABLE_PREFIX ."products_prices`
    WHERE Key_name = 'product_price'
    AND Non_unique = 0;"
  );

  if (database::num_rows($index_query)) {
    database::query(
      "ALTER TABLE `". DB_TABLE_PREFIX ."products_prices`
      DROP KEY `product_price`;"
    );
  }

  $index_query = database::query(
    "SHOW KEYS FROM `". DB_TABLE_PREFIX ."products_to_categories`
    WHERE Key_name = 'mapping'
    AND Non_unique = 0;"
  );

  if (database::num_rows($index_query)) {
    database::query(
      "ALTER TABLE `". DB_TABLE_PREFIX ."products_to_categories`
      DROP KEY `mapping`;"
    );
  }

// Migrate product groups to product attributes
  $products_query = database::query(
    "select id, product_groups from `". DB_TABLE_PREFIX ."products`
    where product_groups != ''
    order by id;"
  );

  while ($product = database::fetch($products_query)) {
    foreach (explode(',', $product['product_groups']) as $product_group) {
      list($group_id, $value_id) = explode('-', $product_group);

      database::query(
        "insert into `". DB_TABLE_PREFIX ."products_attributes`
        (product_id, group_id, value_id) values
        (". (int)$product['id'] .", ". (int)$group_id .", ". (int)$value_id .");"
      );
    }
  }

// Migrate product groups to category filters
  $categories_query = database::query(
    "select id from `". DB_TABLE_PREFIX ."categories`
    order by id;"
  );

  while ($category = database::fetch($categories_query)) {
    $products_attributes_query = database::query(
      "select distinct group_id from `". DB_TABLE_PREFIX ."products_attributes`
      where product_id in (
        select id from `". DB_TABLE_PREFIX ."products_to_categories`
        where category_id = ". (int)$category['id'] ."
      )
      order by group_id;"
    );

    while ($attribute = database::fetch($products_attributes_query)) {
      database::query(
        "insert into `". DB_TABLE_PREFIX ."categories_filters`
        (category_id, attribute_group_id, select_multiple) values
        (". (int)$category['id'] .", ". (int)$attribute['group_id'] .", 1);"
      );
    }
  }

// Finally remove product_groups column
  database::query(
    "alter table `". DB_TABLE_PREFIX ."products`
    drop column `product_groups`;"
  );
