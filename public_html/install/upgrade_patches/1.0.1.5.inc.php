<?php
  $deleted_files = array(
    FS_DIR_APP . 'includes/modules/jobs/job_currency_updater.inc.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

// Update .htaccess Rewrite Rule
  $contents = file_get_contents(FS_DIR_APP . '.htaccess');
  $contents = str_replace('  RewriteRule ^(?:[a-z]{2}/)?.*-p-([0-9]+)$ product.php?product_id=$1&%{QUERY_STRING} [L]', '  RewriteRule ^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$ product.php?category_id=$1&product_id=$2&%{QUERY_STRING} [L]', $contents);
  file_put_contents(FS_DIR_APP . '.htaccess', $contents);
