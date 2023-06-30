<?php

// Modify some files
  perform_action('modify', [
    FS_DIR_APP . 'includes/config.inc.php' => [
      [
        'search'  => "define('DOCUMENT_ROOT',      str_replace('\\\\', '/', rtrim(realpath(!empty(\$_SERVER['DOCUMENT_ROOT']) ? \$_SERVER['DOCUMENT_ROOT'] : __DIR__.'/..'), '/')));",
        'replace' => "define('DOCUMENT_ROOT',      rtrim(str_replace('\\\\', '/', realpath(!empty(\$_SERVER['DOCUMENT_ROOT']) ? \$_SERVER['DOCUMENT_ROOT'] : __DIR__.'/..')), '/'));",
      ],
      [
        'search'  => "define('FS_DIR_APP',         str_replace('\\\\', '/', rtrim(realpath(__DIR__.'/..'), '/')) . '/');",
        'replace' => "define('FS_DIR_APP',         rtrim(str_replace('\\\\', '/', realpath(__DIR__.'/..')), '/') . '/');",
      ],
    ],
  ]);