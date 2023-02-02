<?php

// Delete some files
  perform_action('delete', [
    FS_DIR_APP . 'ext/jquery/jquery-3.6.1.min.js',
    FS_DIR_APP . 'includes/modules/customer/cm_google_maps.inc.php',
    FS_DIR_APP . 'includes/modules/customer/cm_local_database.inc.php',
    FS_DIR_APP . 'includes/library/lib_form.inc.php',
  ], 'skip');

// Modify some files
  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => '  //if (is_writable(__FILE__)) chmod(__FILE__, 0444);' . PHP_EOL . PHP_EOL,
        'replace' => '',
      ],
      [
        'search'  => "~## Backwards Compatible Directory Definitions \(LiteCart <2\.2\)  #+\s+#+\s+.*?#+\R+~s" . PHP_EOL,
        'replace' => "",
        'regex'   => true,
      ],
      [
        'search'  => "  define('DB_PERSISTENT_CONNECTIONS', 'false');" . PHP_EOL,
        'replace' => "",
      ],
      [
        'search'  => "  define('DB_CONNECTION_CHARSET', (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? 'latin1' : 'utf8'); // utf8 or latin1" . PHP_EOL,
        'replace' => "  define('DB_CONNECTION_CHARSET', 'utf8');",
      ],
      [
        'search'  => "// Database Tables - Backwards Compatibility (LiteCart <2.3) (Add-ons)#",
        'replace' => "// Database Tables (Add-ons)",
      ],
      [
        'search'  => "// Database tables (Add-ons)#",
        'replace' => "// Database Tables (Add-ons)",
      ],
      [
        'search'  => "#if \(in_array\(\$_SERVER\['REMOTE_ADDR'\], array\(([^\)]+)\)\)\) \{#",
        'replace' => "if (!isset(\$_SERVER['REMOTE_ADDR']) || in_array(\$_SERVER['REMOTE_ADDR'], [$1])) {",
        'regex'   => true,
      ],
      [
        'search'  => "    error_reporting(E_ALL);" . PHP_EOL,
        'replace' => "",
        'regex'   => true,
      ],
    ],
  ]);

// Copy Payson to swedish installations
  $setting_query = database::query(
    "select `value` from ". DB_PREFIX ."settings
    where `key` = 'store_country_code'
    limit 1;"
  );

  $store_country_code = database::fetch($setting_query, 'value');

  if ($store_country_code == 'SE') {
    perform_action('copy', [
      FS_DIR_APP . 'install/data/default/public_html/includes/modules/payment/pm_payson.inc.php' => FS_DIR_APP . 'includes/modules/payment/',
      FS_DIR_APP . 'install/data/default/public_html/ext/payson/' => FS_DIR_APP . 'ext/payson/',
    ]);
  }