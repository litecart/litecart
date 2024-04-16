<?php

  return [
    'name' => language::translate('title_translations', 'Translations'),
    'default' => 'translations',
    'priority' => 0,

    'theme' => [
      'color' => '#c14a4a',
      'icon' => 'fa-book',
    ],

    'menu' => [
      [
        'title' => language::translate('title_translations', 'Translations'),
        'doc' => 'translations',
        'params' => [],
      ],
      [
        'title' => language::translate('title_scan_files', 'Scan Files'),
        'doc' => 'scan',
        'params' => [],
      ],
      [
        'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
        'doc' => 'csv',
        'params' => [],
      ],
    ],

    'docs' => [
      'translations' => 'translations.inc.php',
      'scan' => 'scan.inc.php',
      'csv' => 'csv.inc.php',
    ],
  ];
