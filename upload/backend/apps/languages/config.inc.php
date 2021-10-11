<?php

  return $app_config = [
    'name' => language::translate('title_languages', 'Languages'),
    'default' => 'languages',
    'priority' => 0,

    'theme' => [
      'color' => '#2b6ca2',
      'icon' => 'fa-comments',
    ],

    'menu' => [
      [
        'title' => language::translate('title_languages', 'Languages'),
        'doc' => 'languages',
        'params' => [],
      ],
      [
        'title' => language::translate('title_storage_encoding', 'Storage Encoding'),
        'doc' => 'storage_encoding',
        'params' => [],
      ],
    ],

    'docs' => [
      'languages' => 'languages.inc.php',
      'edit_language' => 'edit_language.inc.php',
      'storage_encoding' => 'storage_encoding.inc.php',
    ],
  ];
