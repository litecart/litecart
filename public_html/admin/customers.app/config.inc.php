<?php

$app_config = array(
  'name' => language::translate('title_customers', 'Customers'),
  'default' => 'customers',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'title' => language::translate('title_customers', 'Customers'),
      'doc' => 'customers',
      'params' => array(),
    ),
    array(
      'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
      'doc' => 'csv',
      'params' => array(),
    ),
    array(
      'title' => language::translate('title_newsletter', 'Newsletter'),
      'doc' => 'newsletter',
      'params' => array(),
    ),
  ),
  'docs' => array(
    'customers' => 'customers.inc.php',
    'edit_customer' => 'edit_customer.inc.php',
    'csv' => 'csv.inc.php',
    'newsletter' => 'newsletter.inc.php',
  ),
);

?>