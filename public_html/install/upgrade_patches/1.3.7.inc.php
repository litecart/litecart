<?php

// Patch templates
  foreach (glob(FS_DIR_APP . 'includes/templates/*/layouts/*.inc.php') as $file) {
    if (preg_match('#default\.(admin|catalog)#', $file)) continue;

    perform_action('modify', [
      $file => [
        [
          'search'  => '#'. preg_quote('<!--snippet:javascript-->', '#') .'#',
          'replace' => '<!--snippet:style-->',
        ],
        [
          'search'  => '#'. preg_quote('</body>', '#') .'#',
          'replace' => '<!--snippet:javascript-->',
        ],
      ],
    ]);
  }

  foreach (glob(FS_DIR_APP . 'includes/templates/*.catalog/views/box_site_footer.inc.php') as $file) {
    if (preg_match('#default\.catalog#', $file)) continue;

    perform_action('modify', [
      $file => [
        [
          'search'  => '#'. preg_quote('<?php list($account, $domain) = explode(\'@\', settings::get(\'store_email\')); echo "<script>document.write(\'<a href=\\"mailto:". $account ."\' + \'@\' + \'". $domain ."\\">". $account ."\' + \'@\' + \'". $domain ."</a>\');</script>"; ?>', '#') .'#',
          'replace' => '<a href="mailto:<?php echo settings::get(\'store_email\'); ?>"><?php echo settings::get(\'store_email\'); ?></a>',
        ],
      ],
    ]);
  }
