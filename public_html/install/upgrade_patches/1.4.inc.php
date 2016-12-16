<?php

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'sales.widget/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'fancybox/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.12.4.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-migrate-1.4.1.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'responsiveslider/',
    FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/images/fancybox/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/styles/fancybox.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/images/fancybox/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/images/cart.png',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/images/cart_filled.png',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/styles/fancybox.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/styles/custom.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/styles/ie.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/styles/ie8.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/styles/ie9.css',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

  file_xcopy('data/default/public_html/images/no_image.png', FS_DIR_HTTP_ROOT . WS_DIR_IMAGES);

  foreach (glob(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . '*.catalog/*.inc.php') as $file) {
    if (preg_match('#default\.catalog#', $file)) continue;
    $contents = file_get_contents($file);
    $contents = preg_replace('#'. preg_quote('fancybox', '#') .'#', 'lightbox', $contents);
    file_put_contents($file, $contents);
  }

?>