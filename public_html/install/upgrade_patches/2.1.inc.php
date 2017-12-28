<?php

// Copy missing short description from description
  $products_info_query = database::query(
    "select id, description from ". DB_TABLE_PRODUCTS_INFO ."
    where short_description = ''
    and description != '';"
  );

  while($product_info = database::fetch($products_info_query)) {

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

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'responsiveslides/',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

// Modify some files
  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . '*.catalog/views/listing_product.inc.php',
      'search'  => '$price',
      'replace' => 'currency::format($regular_price)',
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . '*.catalog/views/listing_product.inc.php',
      'search'  => '$campaign_price',
      'replace' => 'currency::format($campaign_price)',
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => 'define(\'WS_DIR_HTTP_HOME\', str_replace(FS_DIR_HTTP_ROOT, \'\', str_replace(\'\\\\\', \'/\', realpath(dirname(__FILE__) . \'/\' . \'..\') . \'/\')));',
      'replace' => 'define(\'WS_DIR_HTTP_HOME\', rtrim(str_replace(FS_DIR_HTTP_ROOT, \'\', str_replace(\'\\\\\', \'/\', realpath(__DIR__.\'/..\'))), \'/\') . \'/\');',
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }

// Delete old files
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'trumbowyg/plugins/base64/',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
