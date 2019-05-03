<?php

  return $app_config = array(
    'name' => language::translate('title_customers', 'Customers'),
    'default' => 'customers',
    'priority' => 0,
    'theme' => array(
      'color' => '#37b776',
      'icon' => 'fa-user',
    ),
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
      'customer_picker' => 'customer_picker.inc.php',
      'customers' => 'customers.inc.php',
      'customers.json' => 'customers.json.inc.php',
      'csv' => 'csv.inc.php',
      'edit_customer' => 'edit_customer.inc.php',
      'get_address.json' => 'get_address.json.inc.php',
      'newsletter' => 'newsletter.inc.php',
    ),
  );
