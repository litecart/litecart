<?php

// Move vQmods to vMod

  foreach (glob(FS_DIR_APP . 'vqmod/xml/*.{xml,disabled}', GLOB_BRACE) as $file) {
    rename($file, FS_DIR_APP . 'vmods/' . basename($file));
  }

// Delete old files
  $deleted_files = [
    FS_DIR_ADMIN . '.htaccess',
    FS_DIR_ADMIN . '.htpasswd',
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
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  =>   "~## Backwards Compatible Directory Definitions \(LiteCart <2\.2\)  #######\R+"
                   . "######################################################################\R+"
                   . ".*?"
                   . "######################################################################\R+~s",
      'replace' => "",
      'regex' => true,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "~  define\('DB_PERSISTENT_CONNECTIONS', '[^']+'\);(\n\|\r\n?)?~",
      'replace' => "",
      'regex' => true,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "~// Password Encryption Salt\R+"
                 . "  define('PASSWORD_SALT', '[^']+');\R+~s",
      'replace' => "",
      'regex' => true,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "~\s*\?>\s*$~",
      'replace' => PHP_EOL,
      'regex' => true,
    ],
  ];

  foreach ($modified_files as $modification) {

      echo 'Modify '. $file . '<br />' . PHP_EOL;

    if (!file_modify($modification['file'], $modification['search'], $modification['replace'], !empty($modification['regex']))) {
      die('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }

// Copy some files
  file_xcopy(FS_DIR_APP . 'install/data/default/public_html/images/favicons/', FS_DIR_APP . 'images/favicons/');
