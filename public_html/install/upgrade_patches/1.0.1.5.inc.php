<?php

  perform_action('delete', [
    FS_DIR_APP . 'includes/modules/jobs/job_currency_updater.inc.php',
  ]);

  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      [
        'search'  => "  RewriteRule ^(?:[a-z]{2}/)?.*-p-([0-9]+)$ product.php?product_id=$1&%{QUERY_STRING} [L]" . PHP_EOL,
        'replace' => "  RewriteRule ^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$ product.php?category_id=$1&product_id=$2&%{QUERY_STRING} [L]",
      ],
    ],
  ]);
