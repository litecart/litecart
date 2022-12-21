<?php

  perform_action('delete', [
    FS_DIR_ADMIN . 'orders.app/printable_order_copy.inc.php',
    FS_DIR_ADMIN . 'orders.app/printable_packing_slip.inc.php',
    FS_DIR_APP . 'includes/modules/customer/cm_google_maps.inc.php',
    FS_DIR_APP . 'includes/modules/customer/cm_local_database.inc.php',
  ]);

  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => '  <FilesMatch "\.(css)$">',
        'replace' => '  <FilesMatch "\.(css|js)$">',
      ],
      [
        'file'    => FS_DIR_APP . '.htaccess',
        'search'  => '  <FilesMatch "\.(eot|gif|ico|jpg|jpeg|js|otf|pdf|png|svg|ttf|woff|woff2)$">',
        'replace' => '  <FilesMatch "\.(a?png|bmp|eot|gif|ico|jpe?g|jp2|js|otf|pdf|svg|tiff?|ttf|webp|woff2?)$">',
      ],
    ],
  ]);
