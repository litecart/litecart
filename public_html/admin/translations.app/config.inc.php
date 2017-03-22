<?php

  $app_config = array(
    'name' => language::translate('title_translations', 'Translations'),
    'default' => 'search',
    'theme' => array(
      'color' => '#cd9e9e',
      'icon' => 'fa-book',
    ),
    'menu' => array(
      array(
        'title' => language::translate('title_search_translations', 'Search Translations'),
        'doc' => 'search',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_scan_files', 'Scan Files'),
        'doc' => 'scan',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
        'doc' => 'csv',
        'params' => array(),
      ),
    ),
    'docs' => array(
      'search' => 'search.inc.php',
      'scan' => 'scan.inc.php',
      'csv' => 'csv.inc.php',
    ),
  );
