<?php

// Delete old files
  $deleted_files = [
    FS_DIR_APP . 'ext/jquery/jquery-3.5.1.min.js',
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
      'search'  => "  define('DOCUMENT_ROOT',      rtrim(str_replace('\\', '/', realpath(\$_SERVER['DOCUMENT_ROOT'])), '/'));",
      'replace' => "  define('DOCUMENT_ROOT',      str_replace('\\', '/', rtrim(realpath(!empty(\$_SERVER['DOCUMENT_ROOT']) ? \$_SERVER['DOCUMENT_ROOT'] : __DIR__.'/..'), '/')));",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('FS_DIR_APP',         DOCUMENT_ROOT . rtrim(str_replace(DOCUMENT_ROOT, '', str_replace('\\', '/', realpath(__DIR__.'/..'))), '/') . '/');",
      'replace' => "  define('FS_DIR_APP',         str_replace('\\', '/', rtrim(realpath(__DIR__.'/..'), '/')) . '/');",
    ],    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('WS_DIR_APP',         rtrim(str_replace(DOCUMENT_ROOT, '', str_replace('\\', '/', realpath(__DIR__.'/..'))), '/') . '/');",
      'replace' => "  define('WS_DIR_APP',         preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', FS_DIR_APP));",
    ],
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "// Database tables",
      'replace' => "// Database Tables - Backwards Compatibility (LiteCart <2.3)",
    ],
  ];

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }

// Connect guest orders to account (if applicable)
  $orders_query = database::query(
    "select customer_id, customer_email from ". DB_TABLE_PREFIX ."orders
    where customer_id = 0;"
  );

  while ($order = database::fetch($orders_query)) {
    $customers_query = database::query(
      "select id from ". DB_TABLE_PREFIX ."customers
      where lower(email) = lower('". database::input($order['customer_email']) ."');"
    );

    if ($customer = database::fetch($customers_query)) {
      database::query(
        "update ". DB_TABLE_PREFIX ."orders
        set customer_id = '". database::input($customer['id']) ."'
        where lower(customer_email) = lower('". database::input($order['customer_email']) ."');"
      );
    }
  }
