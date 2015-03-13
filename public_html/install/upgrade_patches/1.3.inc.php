<?php
  
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