<?php

  perform_action('delete', [
    FS_DIR_APP . 'ext/jquery/jquery-3.7.1.min.js',
    FS_DIR_APP . 'includes/modules/jobs/job_cache_cleaner.inc.php',
  ]);

// Block traversal requests in .htaccess (for security reasons)
  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search' => '  # No rewrite logic for physical files',
        'replace' => implode(PHP_EOL, [
          '  # Block traversal request (for security reasons)',
          '  RewriteCond %{REQUEST_URI} (?:%2e|\.)(?:%2e|\.)(?:[/\\%]|$) [NC]',
          '  RewriteRule ^ - [F]',
          '',
          '  # No rewrite logic for physical files',
        ]),
      ]
    ],
  ], 'skip');

// Convert serialized cart item options to JSON
  $cart_items_query = database::query(
    "select id, options
    from ". DB_TABLE_PREFIX ."cart_items
    where options is not null and options != '';"
  )->each(function($cart_item) {
    $unserialized = @unserialize($cart_item['options'], ['allowed_classes' => false]);
    database::query(
      "update ". DB_TABLE_PREFIX ."cart_items
      set options = '". database::input(json_encode($unserialized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ."'
      where id = ". (int)$cart_item['id'] ."
      limit 1;"
    );
  });

// Convert serialized order item options to JSON
  $order_items_query = database::query(
    "select id, options
    from ". DB_TABLE_PREFIX ."orders_items
    where options is not null and options != '';"
  )->each(function($order_item) {
    $unserialized = @unserialize($order_item['options'], ['allowed_classes' => false]);
    database::query(
      "update ". DB_TABLE_PREFIX ."orders_items
      set options = '". database::input(json_encode($unserialized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ."'
      where id = ". (int)$order_item['id'] ."
      limit 1;"
    );
  });
