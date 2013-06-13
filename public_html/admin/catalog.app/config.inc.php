<?php

$app_config = array(
  'name' => $system->language->translate('title_catalog', 'Catalog'),
  'default' => 'catalog',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'title' => $system->language->translate('title_catalog', 'Catalog'),
      'doc' => 'catalog',
      'params' => array(),
    ),
    array(
      'title' => $system->language->translate('title_product_groups', 'Product Groups'),
      'doc' => 'product_groups',
      'params' => array(),
    ),
    array(
      'title' => $system->language->translate('title_option_groups', 'Option Groups'),
      'doc' => 'option_groups',
      'params' => array(),
    ),
    array(
      'title' => $system->language->translate('title_manufacturers', 'Manufacturers'),
      'doc' => 'manufacturers',
      'params' => array(),
    ),
    array(
      'title' => $system->language->translate('title_suppliers', 'Suppliers'),
      'doc' => 'suppliers',
      'params' => array(),
    ),
    array(
      'title' => $system->language->translate('title_delivery_statuses', 'Delivery Statuses'),
      'doc' => 'delivery_statuses',
      'params' => array(),
    ),
    array(
      'title' => $system->language->translate('title_sold_out_statuses', 'Sold Out Statuses'),
      'doc' => 'sold_out_statuses',
      'params' => array(),
    ),
    array(
      'title' => $system->language->translate('title_csv_import_export', 'CSV Import/Export'),
      'doc' => 'csv',
      'params' => array(),
    ),
  ),
  'docs' => array(
    'catalog' => 'catalog.inc.php',
    'edit_product' => 'edit_product.inc.php',
    'edit_category' => 'edit_category.inc.php',
    'product_groups' => 'product_groups.inc.php',
    'edit_product_group' => 'edit_product_group.inc.php',
    'option_groups' => 'option_groups.inc.php',
    'edit_option_group' => 'edit_option_group.inc.php',
    'manufacturers' => 'manufacturers.inc.php',
    'edit_manufacturer' => 'edit_manufacturer.inc.php',
    'suppliers' => 'suppliers.inc.php',
    'edit_supplier' => 'edit_supplier.inc.php',
    'delivery_statuses' => 'delivery_statuses.inc.php',
    'edit_delivery_status' => 'edit_delivery_status.inc.php',
    'sold_out_statuses' => 'sold_out_statuses.inc.php',
    'edit_sold_out_status' => 'edit_sold_out_status.inc.php',
    'csv' => 'csv.inc.php',
  ),
);

?>