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
    ],
    [
      'search'  => '<FilesMatch "\.(a?png|avif|bmp|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
      'replace' => '<FilesMatch "\.(a?png|avif|bmp|css|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
      'regex'   => false,
    ],
  ], 'skip');
