<?php

  $result = [
    'name' => language::translate('title_customers', 'Customers'),
    'results' => [],
  ];

  $result['results'] = database::query(
    "select id, concat(firstname, ' ', lastname) as name, email,
    (
      if(id = '". database::input($query) ."', 10, 0)
      + if(email like '%". database::input($query) ."%', 5, 0)
      + if(tax_id like '%". database::input($query) ."%', 5, 0)
      + if(concat(company, ' ', firstname, ' ', lastname, ' ', address1, ' ', address2, ' ', postcode, ' ', city) like '%". database::input($query) ."%', 5, 0)
    ) as relevance
    from ". DB_TABLE_PREFIX ."customers
    having relevance > 0
    order by relevance desc, id desc
    limit 5;"
  )->fetch_all(function($customer) {
    return [
      'id' => $customer['id'],
      'title' => $customer['name'],
      'description' => $customer['email'],
      'link' => document::ilink('customers/edit_customer', ['customer_id' => $customer['id']]),
    ];
  });

  return [$result];
