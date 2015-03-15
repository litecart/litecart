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
        "insert into ". DB_TABLE_PRODUCTS_TO_CATEGORIES ."
        (product_id, category_id)
         values ('". (int)$product['id'] ."', '". (int)$category_id ."');"
      );
      $is_first = false;
    }
  }
  
  /*
  $deleted_files = array(
    //FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '',
  );
  
  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
  */
  
  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "define('DB_TABLE_SEO_LINKS_CACHE',                   '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'seo_links_cache`');" . PHP_EOL,
      'replace' => "",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('DB_TABLE_PRODUCTS_PRICES',                   '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products_prices`');",
      'replace' => "  define('DB_TABLE_PRODUCTS_PRICES',                   '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'products_prices`');" . PHP_EOL
                 . "  define('DB_TABLE_QUANTITY_UNITS',                    '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'quantity_units`');" . PHP_EOL
                 . "  define('DB_TABLE_QUANTITY_UNITS_INFO',               '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'quantity_units_info`');",
    ),
  );
  
  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }

?>