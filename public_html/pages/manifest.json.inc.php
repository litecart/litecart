<?php

  $manifest = [
    'name' => settings::get('store_name'),
    'start_url' => document::ilink(''),
    'display' => 'standalone',
    'orientation' => 'portrait-primary',

    'icons' => [
      [
        'src' => document::rlink(FS_DIR_APP . 'images/favicons/favicon.ico'),
        'sizes' => '32x32 48x48 64x64 96x96',
        'type' => 'image/x-icon',
      ],
      [
        'src' => document::rlink(FS_DIR_APP . 'images/favicons/favicon-128x128.png'),
        'sizes' => '128x128',
        'type' => 'image/png',
      ],
      [
        'src' => document::rlink(FS_DIR_APP . 'images/favicons/favicon-192x192.png'),
        'sizes' => '192x192',
        'type' => 'image/png',
      ],
      [
        'src' => document::rlink(FS_DIR_APP . 'images/favicons/favicon-256x256.png'),
        'sizes' => '256x256',
        'type' => 'image/png',
      ],
    ],

    'shortcuts' => [
      [
        'name' => language::translate('title_categories', 'Categories'),
        'url' => document::ilink('categories'),
      ],
      [
        'name' => language::translate('title_manufacturers', 'Manufacturers'),
        'url' => document::ilink('manufacturers'),
      ],
      [
        'name' => language::translate('title_customer_service', 'Customer Service'),
        'url' => document::ilink('customer_service'),
      ],
    ],
  ];

  header('Content-Type: application/manifest+json; charset='. language::$selected['charset']);
  echo json_encode($manifest,  JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  exit;
