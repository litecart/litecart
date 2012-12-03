<?php

$app_config = array(
  'name' => $system->language->translate('title_translations', 'Translations'),
  'index' => 'search.php',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'name' => $system->language->translate('title_search_translations', 'Search Translations'),
      'link' => 'search.php'
    ),
    array(
      'name' => $system->language->translate('title_untranslated', 'Untranslated'),
      'link' => 'untranslated.php'
    ),
    array(
      'name' => $system->language->translate('title_translations_by_page', 'Translations by page'),
      'link' => 'pages.php'
    ),
    array(
      'name' => $system->language->translate('title_scan_files', 'Scan Files'),
      'link' => 'scan.php'
    ),
    array(
      'name' => $system->language->translate('title_csv_import_export', 'CSV Import/Export'),
      'link' => 'csv.php'
    ),
  ),
);

?>