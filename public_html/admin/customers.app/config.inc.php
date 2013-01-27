<?php

$app_config = array(
  'name' => $system->language->translate('title_customers', 'Customers'),
  'index' => 'customers.php',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'name' => $system->language->translate('title_customers', 'Customers'),
      'link' => 'customers.php'
    ),
    array(
      'name' => $system->language->translate('title_csv_import_export', 'CSV Import/Export'),
      'link' => 'csv.php'
    ),
    array(
      'name' => $system->language->translate('title_newsletter', 'Newsletter'),
      'link' => 'newsletter.php'
    ),
  ),
);

?>