<?php

  return $app_config = array(
    'name' => language::translate('title_catalog', 'Catalog'),
    'default' => 'catalog',
    'priority' => 0,
    'theme' => array(
      'color' => '#d0cb2b',
      'icon' => 'fa-th',
    ),
    'menu' => array(
      array(
        'title' => language::translate('title_catalog', 'Catalog'),
        'doc' => 'catalog',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_attribute_groups', 'Attribute Groups'),
        'doc' => 'attribute_groups',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_option_groups', 'Option Groups'),
        'doc' => 'option_groups',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_manufacturers', 'Manufacturers'),
        'doc' => 'manufacturers',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_suppliers', 'Suppliers'),
        'doc' => 'suppliers',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_delivery_statuses', 'Delivery Statuses'),
        'doc' => 'delivery_statuses',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_sold_out_statuses', 'Sold Out Statuses'),
        'doc' => 'sold_out_statuses',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_quantity_units', 'Quantity Units'),
        'doc' => 'quantity_units',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
        'doc' => 'csv',
        'params' => array(),
      ),
    ),
    'docs' => array(
      'attribute_groups' => 'attribute_groups.inc.php',
      'attribute_values.json' => 'attribute_values.json.inc.php',
      'catalog' => 'catalog.inc.php',
      'edit_attribute_group' => 'edit_attribute_group.inc.php',
      'edit_product' => 'edit_product.inc.php',
      'edit_category' => 'edit_category.inc.php',
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
      'quantity_units' => 'quantity_units.inc.php',
      'edit_quantity_unit' => 'edit_quantity_unit.inc.php',
      'csv' => 'csv.inc.php',
      'products.json' => 'products.json.inc.php',
    ),
  );
