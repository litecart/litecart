<?php

// Move some files
  foreach (glob(FS_DIR_APP . 'vqmod/xml/*') as $file) {
    if (is_file())
    copy($file, FS_DIR_APP . 'vmods/');
  }

// Delete old files
  $deleted_files = [
    FS_DIR_ADMIN . 'customers.app/mailchimp.png',
    FS_DIR_APP . 'includes/functions/func_password.inc.php',
    FS_DIR_APP . 'ext/jquery/jquery-3.4.1.min.js',
    FS_DIR_APP . 'vqmod/',
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
      'search'  => "SetEnv HTTP_MOD_REWRITE On",
      'replace' => "SetEnv MOD_REWRITE On",
    ],    [
      'file'    => FS_DIR_APP . '.htaccess',
      'search'  => "RewriteRule ^.*$ index.php?%{QUERY_STRING} [L]",
      'replace' => "RewriteRule ^.*$ index.php [QSA,L]",
    ],
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
      'search'  => "	define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL,
      'replace' => "	define('FS_DIR_ADMIN',       FS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL
                 . "	define('FS_DIR_STORAGE',     FS_DIR_APP . '/');" . PHP_EOL,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "	define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL,
      'replace' => "	define('WS_DIR_ADMIN',       WS_DIR_APP . BACKEND_ALIAS . '/');" . PHP_EOL
                 . "	define('WS_DIR_STORAGE',     WS_DIR_APP . '/');" . PHP_EOL,
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "## Backwards Compatible Directory Definitions (LiteCart <2.2)  #######",
      'replace' => "## Backward Compatible Directory Definitions (LiteCart <2.2) #########",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'pattern'  => '#'. preg_quote('## Backward Compatible Directory Definitions (LiteCart <2.2)', '#') .'.*?'. preg_quote('## Database ##########################################################', '#') .'#',
      'replace' => '## Database ##########################################################',
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "// Database tables",
      'replace' => "// Database Tables - Backward Compatibility (LiteCart <2.3)",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'pattern'  => '#'. preg_quote(PHP_EOL, '#') .'// Password Encryption Salt.*?\);'. preg_quote(PHP_EOL, '#') .'#m',
      'replace' => '',
    ],
  ];

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }

// Adjust tables
  $columns_query = database::query(
    "select * from information_schema.COLUMNS
    where TABLE_SCHEMA = '". DB_DATABASE ."'
    and TABLE_NAME like '". DB_PREFIX ."%';"
  );

  while ($column = database::fetch($columns_query)) {
    switch ($column['COLUMN_NAME']) {
      case 'id':
        break;

      case 'date_updated':
        database::query(
          "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
          change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` timestamp not null default current_timestamp on update current_timestamp;"
        );
        break;

      case 'date_created':
        database::query(
          "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
          change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` timestamp not null default current_timestamp;"
        );
        break;

      default:
        database::query(
          "alter table ". $column['TABLE_SCHEMA'] .".". $column['TABLE_NAME'] ."
          change column `". $column['COLUMN_NAME'] ."` `". $column['COLUMN_NAME'] ."` ". $column['COLUMN_TYPE'] ." null". (!empty($column['COLUMN_DEFAULT']) ? ' default ' . $column['COLUMN_DEFAULT'] : '') .";"
        );
        break;
    }
  }
