<?php

  perform_action('delete', [
    FS_DIR_APP . 'ext/jquery/jquery-3.5.1.min.js',
  ]);

  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "  define('DOCUMENT_ROOT',      rtrim(str_replace(\"\\\", '/', realpath(\$_SERVER['DOCUMENT_ROOT'])), '/'));",
        'replace' => "  define('DOCUMENT_ROOT',      str_replace('\\', '/', rtrim(realpath(!empty(\$_SERVER['DOCUMENT_ROOT']) ? \$_SERVER['DOCUMENT_ROOT'] : __DIR__.'/..'), '/')));",
      ],
      [
        'search'  => "  define('DOCUMENT_ROOT',      rtrim(str_replace('\\', '/', realpath(\$_SERVER['DOCUMENT_ROOT'])), '/'));",
        'replace' => "  define('DOCUMENT_ROOT',      str_replace('\\', '/', rtrim(realpath(!empty(\$_SERVER['DOCUMENT_ROOT']) ? \$_SERVER['DOCUMENT_ROOT'] : __DIR__.'/..'), '/')));",
      ],
      [
        'search'  => "  define('FS_DIR_APP',         DOCUMENT_ROOT . rtrim(str_replace(DOCUMENT_ROOT, '', str_replace(\"\\\", '/', realpath(__DIR__.'/..'))), '/') . '/');",
        'replace' => "  define('FS_DIR_APP',         str_replace('\\', '/', rtrim(realpath(__DIR__.'/..'), '/')) . '/');",
      ],
      [
        'search'  => "  define('FS_DIR_APP',         DOCUMENT_ROOT . rtrim(str_replace(DOCUMENT_ROOT, '', str_replace('\\', '/', realpath(__DIR__.'/..'))), '/') . '/');",
        'replace' => "  define('FS_DIR_APP',         str_replace('\\', '/', rtrim(realpath(__DIR__.'/..'), '/')) . '/');",
      ],
      [
        'search'  => "  define('WS_DIR_APP',         rtrim(str_replace(DOCUMENT_ROOT, '', str_replace(\"\\\", '/', realpath(__DIR__.'/..'))), '/') . '/');",
        'replace' => "  define('WS_DIR_APP',         preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', FS_DIR_APP));",
      ],
      [
        'search'  => "  define('WS_DIR_APP',         rtrim(str_replace(DOCUMENT_ROOT, '', str_replace('\\', '/', realpath(__DIR__.'/..'))), '/') . '/');",
        'replace' => "  define('WS_DIR_APP',         preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', FS_DIR_APP));",
      ],
      [
        'search'  => "// Database tables",
        'replace' => "// Database Tables - Backwards Compatibility (LiteCart <2.3)",
      ],
      [
        'search'  => "// Database tables",
        'replace' => "// Database Tables - Backwards Compatibility (LiteCart <2.3)",
      ],
    ],
  ]);

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
