<?php

  return $app_config = [

    'name' => language::translate('title_customers', 'Customers'),
    'default' => 'customers',
    'priority' => 0,

    'theme' => [
      'color' => '#21a261',
      'icon' => 'fa-user',
    ],

    'menu' => [
      [
        'title' => language::translate('title_customers', 'Customers'),
        'doc' => 'customers',
        'params' => [],
      ],
      [
        'title' => language::translate('title_newsletter_recipients', 'Newsletter Recipients'),
        'doc' => 'newsletter_recipients',
        'params' => [],
      ],
      [
        'title' => language::translate('title_csv_import_export', 'CSV Import/Export'),
        'doc' => 'csv',
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

    'search' => function ($query) {

      $result = [
        'name' => language::translate('title_customers', 'Customers'),
        'results' => [],
      ];

      $customers_query = database::query(
        "select id, concat(firstname, ' ', lastname) as name, email,
        (
          if(id = '". database::input($query) ."', 10, 0)
          + if(email like '%". database::input($query) ."%', 5, 0)
          + if(tax_id like '%". database::input($query) ."%', 5, 0)
          + if(concat(company, ' ', firstname, ' ', lastname, ' ', address1, ' ', address2, ' ', postcode, ' ', city) like '%". database::input($query) ."%', 5, 0)
          + if(concat(shipping_company, ' ', shipping_firstname, ' ', shipping_lastname, ' ', shipping_address1, ' ', shipping_address2, ' ', shipping_postcode, ' ', shipping_city) like '%". database::input($_GET['query']) ."%', 5, 0)
        ) as relevance
        from ". DB_TABLE_PREFIX ."customers
        having relevance > 0
        order by relevance desc, id desc
        limit 5;"
      );

      if (!database::num_rows($customers_query)) return;

      while ($customer = database::fetch($customers_query)) {
        $result['results'][] = [
          'id' => $customer['id'],
          'title' => $customer['name'],
          'description' => $customer['email'],
          'link' => document::ilink('customers/edit_customer', ['customer_id' => $customer['id']]),
        ];
      }

      return [$result];
    },
  ];
