<?php

  $modified_files = array(
    array(
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_CATEGORIES_IMAGES',                 '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'categories_images`');" . PHP_EOL,
      'replace' => "",
    ),
  );

  $categories_images_query = database::query(
    "select id from `". DB_TABLE_PREFIX ."categories_images`
    group by category_id
    having count(*) >= 2;"
  );

  if (database::num_rows($categories_images_query)) {

    $modified_files[] = array(
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "// Database tables (Add-ons)" . PHP_EOL,
      'replace' => "// Database tables (Add-ons)" . PHP_EOL
                 . "  define('DB_TABLE_CATEGORIES_IMAGES',                 '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'categories_images`');" . PHP_EOL,
    );

    copy(FS_DIR_APP . 'install/other/multiple_category_images.xml', FS_DIR_APP . 'vqmod/xml/multiple_category_images.xml');
  }

// Modify some files
  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }
