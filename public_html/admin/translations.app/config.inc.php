<?php

  $app_config = array(
    'name' => language::translate('title_translations', 'Translations'),
    'default' => 'search',
    'theme' => array(
      'color' => '#cd9e9e',
      'icon' => 'book',
    ),
    'menu' => array(
      array(
        'title' => language::translate('title_search_translations', 'Search Translations'),
        'doc' => 'search',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_untranslated', 'Untranslated'),
        'doc' => 'untranslated',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_translations_by_page', 'Translations By Page'),
        'doc' => 'pages',
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
      'untranslated' => 'untranslated.inc.php',
      'pages' => 'pages.inc.php',
      'scan' => 'scan.inc.php',
      'csv' => 'csv.inc.php',
    ),
  );

?>