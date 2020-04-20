<?php

// Move some files
  foreach (glob(FS_DIR_APP . 'vqmod/xml/*') as $file) {
    if (is_file())
    copy($file, FS_DIR_APP . 'vmods/');
  }

// Delete old files
  $deleted_files = [
    FS_DIR_ADMIN . 'customers.app/mailchimp.png',
    FS_DIR_ADMIN . 'includes/functions/func_password.inc.php',
    FS_DIR_ADMIN . 'vqmod/',
    FS_DIR_APP . 'ext/jquery/jquery-3.4.1.min.js',
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
      'search'  => "DB_TABLE_PREFIX",
      'replace' => "DB_PREFIX",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_PREFIX . 'manufacturers_info`');" . PHP_EOL,
      'replace' => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_PREFIX . 'manufacturers_info`');" . PHP_EOL
                 . "  define('DB_TABLE_NEWSLETTER_RECIPIENTS',             '`'. DB_DATABASE .'`.`'. DB_PREFIX . 'newsletter_recipients`');" . PHP_EOL,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_CATEGORIES_INFO',                   '`'. DB_DATABASE .'`.`'. DB_PREFIX . 'categories_info`');" . PHP_EOL,
      'replace' => "",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "## Backwards Compatible Directory Definitions (LiteCart <2.2)  #######",
      'replace' => "## Backward Compatible Directory Definitions (LiteCart <2.2)   #######",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "// Database tables",
      'replace' => "// Database Tables - Backward Compatibility (LiteCart <2.3)",
    ],
  ];

// See if multiple category images is used
  $categories_images_query = database::query(
    "select count(id) from `". DB_PREFIX ."categories_images`
    group by category_id
    having count(*) >= 2"
  );

  if (database::num_rows($categories_images_query)) {
    $modified_files[] = [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "// Database tables (Add-ons)" . PHP_EOL,
      'replace' => "// Database tables (Add-ons)" . PHP_EOL
                 . "  define('DB_TABLE_CATEGORIES_INFO',                   '`'. DB_DATABASE .'`.`'. DB_PREFIX . 'categories_info`');" . PHP_EOL,
    ];
    copy(FS_DIR_APP . 'install/data/other/multiple_category_images.xml', FS_DIR_APP . 'vmods/multiple_category_images.xml');
  } else {
    database::query(
      "drop table `". DB_PREFIX ."categories_images`;"
    );
  }

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }

  $modified_files = [
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'pattern'  => '#'. preg_quote(PHP_EOL, '#') .'// Password Encryption Salt.*?\);'. preg_quote(PHP_EOL, '#') .'#m',
      'replace' => '',
    ],
  ];

  foreach ($modified_files as $modification) {
    if (!file_modify_regex($modification['file'], $modification['search'], $modification['replace'])) {
      echo '<span class="error">[Skip]</span><br />Could not find: '. $modification['search'] .'</p>';
    }
  }
