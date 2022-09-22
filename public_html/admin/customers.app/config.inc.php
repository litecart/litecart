<?php

  return $app_config = [
    'name' => language::translate('title_customers', 'Customers'),
    'default' => 'customers',
    'priority' => 0,
    'theme' => [
      'color' => '#50c187',
      'icon' => 'fa-user',
    ],
    'menu' => [
      [
        'title' => language::translate('title_customers', 'Customers'),
        'doc' => 'customers',
        'params' => [],
      ],
      [
        'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
        'doc' => 'csv',
        'params' => [],
      ],
      [
        'title' => language::translate('title_newsletter_recipients', 'Newsletter Recipients'),
        'doc' => 'newsletter_recipients',
        'params' => [],
      ],
    ],
    'docs' => [
      'customer_picker' => 'customer_picker.inc.php',
      'customers' => 'customers.inc.php',
      'customers.json' => 'customers.json.inc.php',
      'csv' => 'csv.inc.php',
      'edit_customer' => 'edit_customer.inc.php',
      'get_address.json' => 'get_address.json.inc.php',
      'newsletter_recipients' => 'newsletter_recipients.inc.php',
    ],
  ];
