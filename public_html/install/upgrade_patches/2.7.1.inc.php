<?php

// Block traversal requests in .htaccess (for security reasons)
  perform_action('modify', [
    FS_DIR_APP . '.htaccess' => [
      'search' => '  # No rewrite logic for physical files',
      'replace' => implode(PHP_EOL, [
        '  # Block traversal request (for security reasons)',
        '  RewriteCond %{REQUEST_URI} (?:%2e|\.)(?:%2e|\.)(?:[/\\%]|$) [NC]',
        '  RewriteRule ^ - [F]',
        '',
        '  # No rewrite logic for physical files',
      ]),
    ],
  ], 'skip');
