<?php

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

?>