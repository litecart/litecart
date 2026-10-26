<?php

  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "error_reporting(version_compare(PHP_VERSION, '5.4.0', '>=') ? E_ALL & ~E_STRICT : E_ALL);",
        'replace' => "error_reporting(E_ALL);",
        'regex'   => false,
      ],
    ],
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => '<FilesMatch "\.(a?png|avif|bmp|css|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
        'replace' => '<FilesMatch "\.(a?png|avif|bmp|css|eot|gif|ico|jpe?g|jp2|js|jxl|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
        'regex'   => false,
      ],
    ],
  ], 'skip');

