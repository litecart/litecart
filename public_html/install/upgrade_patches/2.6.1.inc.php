<?php

  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => implode(PHP_EOL, [
          '  <FilesMatch "\.(css|js)$">',
          '      Header set Cache-Control "max-age=86400, public, must-revalidate"',
          '  </FilesMatch>',
          '',
        ]),
        'replace' => '',
        'regex'   => false,
      ],
      [
        'search'  => '<FilesMatch "\.(a?png|avif|bmp|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
        'replace' => '<FilesMatch "\.(a?png|avif|bmp|css|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
        'regex'   => false,
      ],
    ],
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "error_reporting(version_compare(PHP_VERSION, '5.4.0', '<') ? E_ALL | E_STRICT : E_ALL)",
        'replace' => "error_reporting(E_ALL);",
        'regex'   => false,
      ],
    ]
  ], 'skip');
