<?php

// Modify some files
  $modified_files = [
    [
      'file'    => FS_DIR_APP . 'includes/config.inc.php',
      'search'  => "  define('DOCUMENT_ROOT',      rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/'));",
      'replace' => "  define('DOCUMENT_ROOT',      str_replace('\\', '/', rtrim(realpath(!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : __DIR__.'/..'), '/')));",
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
