<?php

  $products_query =  $database->query(
    "select id, categories from ". DB_TABLE_PRODUCTS .";"
  );

  while ($product = $database->fetch($products_query)) {
    $categories = explode( ',', $product['categories']);

    $is_first = true;
    foreach ($categories as $category_id) {
      if ($is_first) {
        $database->query(
          "update ". DB_TABLE_PRODUCTS ." set
          default_category_id = ". (int)$category_id . "
          where id = '". (int)$product['id'] ."'
          limit 1;"
        );
      }
      $database->query(
        "insert into `". DB_TABLE_PREFIX ."products_to_categories`
        (product_id, category_id)
        values ('". (int)$product['id'] ."', '". (int)$category_id ."');"
      );
      $is_first = false;
    }
  }

  $database->query(
    "alter table ". DB_TABLE_PRODUCTS ." drop `categories`;"
  );

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'appearance.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'catalog.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'countries.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'currencies.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'customers.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'geo_zones.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'languages.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'modules.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'orders.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'pages.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'reports.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'settings.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'slides.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'tax.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'translations.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'translations.app/pages.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'translations.app/untranslated.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'users.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'vqmods.app/icon.png',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'fancybox/jquery.fancybox-1.3.4.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'nivo-slider/',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/add.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/box.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/calendar.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/cancel.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/collapse.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/delete.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/down.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/download.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/edit.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/expand.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/folder_closed.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/folder_opened.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/home.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/index.html',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/label.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/loading.gif',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/off.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/on.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/preview.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/print.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/remove.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/save.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/settings.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/16x16/up.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/24x24/catalog.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/24x24/database.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/24x24/exit.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/24x24/help.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/24x24/home.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/24x24/index.html',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'icons/24x24/mail.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'includes/templates/default.catalog/images/home.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'includes/templates/default.catalog/images/scroll_up.png',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'includes/templates/default.catalog/images/search.png',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "define('DB_TABLE_SEO_LINKS_CACHE',                   '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'seo_links_cache`');" . PHP_EOL,
      'replace' => "",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('DB_TABLE_PRODUCTS_PRICES',                   '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products_prices`');" . PHP_EOL,
      'replace' => "  define('DB_TABLE_PRODUCTS_PRICES',                   '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products_prices`');" . PHP_EOL
                 . "  define('DB_TABLE_PRODUCTS_TO_CATEGORIES',            '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products_to_categories`');" . PHP_EOL
                 . "  define('DB_TABLE_QUANTITY_UNITS',                    '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'quantity_units`');" . PHP_EOL
                 . "  define('DB_TABLE_QUANTITY_UNITS_INFO',               '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'quantity_units_info`');" . PHP_EOL,
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }

?>