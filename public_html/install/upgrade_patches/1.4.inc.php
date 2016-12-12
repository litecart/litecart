<?php

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'sales.widget/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'fancybox/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.12.4.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-migrate-1.4.1.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'responsiveslider/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/images/fancybox/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/images/fancybox/',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/styles/ie.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/styles/ie8.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/styles/ie9.css',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

// Patch templates
  foreach (glob(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . '*/layouts/*.inc.php') as $file) {
    if (preg_match('#default\.(admin|catalog)#', $file)) continue;
    $contents = file_get_contents($file);
    $contents = preg_replace('#'. preg_quote('<!--snippet:javascript-->', '#') .'#', '<!--snippet:style-->', $contents);
    $contents = preg_replace('#'. preg_quote('</body>', '#') .'#', '<!--snippet:javascript-->' . PHP_EOL . '</body>', $contents);
    file_put_contents($file, $contents);
  }

  foreach (glob(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . '*.catalog/views/box_site_footer.inc.php') as $file) {
    if (preg_match('#default\.catalog#', $file)) continue;
    $contents = file_get_contents($file);
    $find = '<?php list($account, $domain) = explode(\'@\', settings::get(\'store_email\')); echo "<script>document.write(\'<a href=\\"mailto:". $account ."\' + \'@\' + \'". $domain ."\\">". $account ."\' + \'@\' + \'". $domain ."</a>\');</script>"; ?>';
    $replace = '<a href="mailto:<?php echo settings::get(\'store_email\'); ?>"><?php echo settings::get(\'store_email\'); ?></a>';
    $contents = preg_replace('#'. preg_quote($search, '#') .'#', $replace, $contents);
    file_put_contents($file, $contents);
  }

  foreach (glob(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . '*.catalog/*.inc.php') as $file) {
    if (preg_match('#default\.catalog#', $file)) continue;
    $contents = file_get_contents($file);
    $contents = preg_replace('#'. preg_quote('fancybox', '#') .'#', 'lightbox', $contents);
    file_put_contents($file, $contents);
  }

?>