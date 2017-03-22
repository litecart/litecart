<?php

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmods/logs/',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'vqmods.app/log.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.11.2.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.11.2.min.map',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'stickers/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/styles/ie.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/styles/ie8.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/styles/ie9.css',
    FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_manufacturers_list.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/views/box_manufacturers_list.inc.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => "  define('DB_TABLE_ADDRESSES',                         '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'addresses`');" . PHP_EOL,
      'replace' => "  define('DB_TABLE_ADDRESSES',                         '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'addresses`');" . PHP_EOL
                 . "  define('DB_TABLE_CART_ITEMS',                        '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'cart_items`');" . PHP_EOL,
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }
