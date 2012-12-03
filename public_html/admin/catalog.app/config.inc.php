<?php

$app_config = array(
  'name' => $system->language->translate('title_catalog', 'Catalog'),
  'index' => 'catalog.php',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'name' => $system->language->translate('title_catalog', 'Catalog'),
      'link' => 'catalog.php'
    ),
    array(
      'name' => $system->language->translate('title_designers', 'Designers'),
      'link' => 'designers.php'
    ),
    array(
      'name' => $system->language->translate('title_product_groups', 'Product Groups'),
      'link' => 'product_groups.php'
    ),
    array(
      'name' => $system->language->translate('title_option_groups', 'Option Groups'),
      'link' => 'option_groups.php'
    ),
    array(
      'name' => $system->language->translate('title_manufacturers', 'Manufacturers'),
      'link' => 'manufacturers.php'
    ),
    array(
      'name' => $system->language->translate('title_suppliers', 'Suppliers'),
      'link' => 'suppliers.php'
    ),
    array(
      'name' => $system->language->translate('title_delivery_statuses', 'Delivery Statuses'),
      'link' => 'delivery_statuses.php'
    ),
    array(
      'name' => $system->language->translate('title_sold_out_statuses', 'Sold Out Statuses'),
      'link' => 'sold_out_statuses.php'
    ),
    array(
      'name' => $system->language->translate('title_csv_import_export', 'CSV Import/Export'),
      'link' => 'csv.php'
    ),
  ),
);

?>