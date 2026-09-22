<?php

  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "error_reporting(version_compare(PHP_VERSION, '5.4.0', '>=') ? E_ALL & ~E_STRICT : E_ALL);",
        'replace' => "error_reporting(E_ALL);",
        'regex'   => false,
      ],
    ],
  ], 'skip');
