<?php

// Move vQmods to vMod

  foreach (glob(FS_DIR_APP . 'vqmod/xml/*.{xml,disabled}', GLOB_BRACE) as $file) {
    rename($file, FS_DIR_STORAGE . 'vmods/' . basename($file));
  }

// Delete old files
  $deleted_files = [
    FS_DIR_ADMIN . '.htaccess',
    FS_DIR_ADMIN . '.htpasswd',
    FS_DIR_ADMIN . 'catalog.app/edit_option_group.inc.php',
    FS_DIR_ADMIN . 'catalog.app/edit_product_group.inc.php',
    FS_DIR_ADMIN . 'customers.app/newsletter.inc.php',
    FS_DIR_ADMIN . 'vqmods.app/',
    FS_DIR_APP . 'ext/jquery/jquery-3.6.0.min.js',
    FS_DIR_APP . 'vqmod/',
    FS_DIR_APP . 'vqmod/xml/product_options_stock_notice.xml',
  ];

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      echo '<span class="error">[Skipped]</span></p>';
    }
  }

// Modify some files
  $modified_files = [
    [
      'file'    => FS_DIR_APP . '.htaccess',
      'search'  => "  RewriteRule ^.*$ index.php?%{QUERY_STRING} [L]",
      'replace' => "  RewriteRule ^.*$ index.php [QSA,L]",
      'regex'   => false,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');",
      'replace' => "  define('FS_DIR_STORAGE',     FS_DIR_APP);" . PHP_EOL
                 . "  define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');",
      'regex'   => false,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');",
      'replace' => "  define('WS_DIR_STORAGE',     WS_DIR_APP);" . PHP_EOL
                 . "  define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');",
      'regex'   => false,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "~## Backwards Compatible Directory Definitions \(LiteCart <2\.2\)  #######\R+"
                 . "######################################################################\R+"
                 . ".*?"
                 . "######################################################################\R+~s",
      'replace' => "",
      'regex'   => true,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "~  define\('DB_PERSISTENT_CONNECTIONS', '[^']+'\);(\n\|\r\n?)?~",
      'replace' => "",
      'regex'   => true,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  ini_set('error_log', FS_DIR_APP . 'logs/errors.log');",
      'replace' => "  ini_set('error_log', FS_DIR_STORAGE . 'logs/errors.log');",
      'regex'   => false,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "~// Password Encryption Salt\R+"
                 . "  define('PASSWORD_SALT', '[^']+');\R+~s",
      'replace' => "",
      'regex'   => true,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "~\s*\?>\s*$~",
      'replace' => PHP_EOL,
      'regex'   => true,
    ],
  ];

  foreach ($modified_files as $modification) {

    echo 'Modify '. $file . '<br />' . PHP_EOL;

    if (!file_modify($modification['file'], $modification['search'], $modification['replace'], !empty($modification['regex']))) {
      echo '<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>';
    }
  }

// Copy some files
  file_xcopy(FS_DIR_APP . 'install/data/default/public_html/images/favicons/', FS_DIR_STORAGE . 'images/favicons/');
