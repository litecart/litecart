<?php

// Delete old files
  $deleted_files = array(
    FS_DIR_ADMIN . 'customers.app/mailchimp.png',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      echo '<span class="error">[Skipped]</span></p>';
    }
  }

// Modify some files
  $modified_files = array(
    array(
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'manufacturers_info`');" . PHP_EOL,
      'replace' => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'manufacturers_info`');" . PHP_EOL
                 . "  define('DB_TABLE_NEWSLETTER_RECIPIENTS',             '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'newsletter_recipients`');" . PHP_EOL,
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }

  $modified_files = array(
    array(
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'pattern'  => '#'. preg_quote(PHP_EOL, '#') .'// Password Encryption Salt.*?\);'. preg_quote(PHP_EOL, '#') .'#m',
      'replace' => '',
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify_regex($modification['file'], $modification['search'], $modification['replace'])) {
      echo '<span class="error">[Skip]</span><br />Could not find: '. $modification['search'] .'</p>';
    }
  }
