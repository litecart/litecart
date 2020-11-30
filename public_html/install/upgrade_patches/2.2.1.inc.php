<?php

  perform_action('delete', [
    FS_DIR_ADMIN . 'orders.app/edit_order_item.php',
  ]);

  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "  define('DB_TABLE_CATEGORIES_IMAGES',                 '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'categories_images`');" . PHP_EOL,
        'replace' => "",
      ],
    ],
  ];

  $categories_images_query = database::query(
    "select id from `". DB_TABLE_PREFIX ."categories_images`
    group by category_id
    having count(*) >= 2;"
  );

  if (database::num_rows($categories_images_query)) {

    perform_action('modify', [
      FS_DIR_APP . 'includes/config.inc.php' => [
        [
          'search'  => "// Database tables (Add-ons)" . PHP_EOL,
          'replace' => "// Database tables (Add-ons)" . PHP_EOL
                     . "  define('DB_TABLE_CATEGORIES_IMAGES',                 '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'categories_images`');" . PHP_EOL,
        ],
      ],
    ], 'abort');

    copy(FS_DIR_APP . 'install/data/other/multiple_category_images.xml', FS_DIR_APP . 'vqmod/xml/multiple_category_images.xml');
  }
