<?php

  $deleted_files = [
    FS_DIR_APP . 'vqmods/logs/',
    FS_DIR_ADMIN . 'vqmods.app/log.inc.php',
    FS_DIR_APP . 'ext/jquery/jquery-1.11.2.min.js',
    FS_DIR_APP . 'ext/jquery/jquery-1.11.2.min.map',
    FS_DIR_STORAGE . 'images/stickers/',
    FS_DIR_APP . 'includes/templates/default.admin/styles/ie.css',
    FS_DIR_APP . 'includes/templates/default.admin/styles/ie8.css',
    FS_DIR_APP . 'includes/templates/default.admin/styles/ie9.css',
    FS_DIR_APP . 'includes/boxes/box_manufacturers_list.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/views/box_manufacturers_list.inc.php',
  ];

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

  $modified_files = [
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_ADDRESSES',                         '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'addresses`');" . PHP_EOL,
      'replace' => "  define('DB_TABLE_ADDRESSES',                         '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'addresses`');" . PHP_EOL
                 . "  define('DB_TABLE_CART_ITEMS',                        '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'cart_items`');" . PHP_EOL,
    ],
  ];

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }
