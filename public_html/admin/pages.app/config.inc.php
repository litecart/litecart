<?php

  $app_config = array(
    'name' => language::translate('title_pages', 'Pages'),
    'default' => 'pages',
    'theme' => array(
      'color' => '#bec6b4',
      'icon' => 'fa-file-text',
    ),
    'menu' => array(
      array(
        'title' => language::translate('title_pages', 'Pages'),
        'doc' => 'pages',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
        'doc' => 'csv',
        'params' => array(),
      ),
    ),
    'docs' => array(
      'pages' => 'pages.inc.php',
      'edit_page' => 'edit_page.inc.php',
      'csv' => 'csv.inc.php',
    ),
  );
