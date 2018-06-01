<?php

// Modify some files
  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'config.inc.php',
      'search'  => '  define(\'DB_TABLE_CATEGORIES\',                        '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . \'categories`\');' . PHP_EOL,
      'replace' => '  define(\'DB_TABLE_CATEGORIES\',                        '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . \'categories`\');' . PHP_EOL
                 . '  define(\'DB_TABLE_CATEGORIES_IMAGES\',                 '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . \'categories_images`\');' . PHP_EOL,
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
