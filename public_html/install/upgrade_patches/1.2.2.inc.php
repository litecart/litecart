<?php

  perform_action('delete', [
    FS_DIR_APP . 'ext/jquery-1.11.1.min.js',
    FS_DIR_APP . 'ext/jquery-1.11.1.min.map',
  ]);

  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "  define('DB_TABLE_SEO_LINKS_CACHE',                   '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'seo_links_cache`');" . PHP_EOL,
        'replace' => "",
      ],
    ],
  ], 'abort');
