<?php

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
