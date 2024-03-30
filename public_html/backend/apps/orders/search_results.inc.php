<?php

  $result = [
    'name' => language::translate('title_orders', 'Orders'),
    'results' => [],
  ];

  $orders_query = database::query(
    "select id, concat(customer_firstname, ' ', customer_lastname) as customer_name,
    (
      if(id = '". database::input($query) ."', 10, 0)
      + if(reference like '%". database::input($query) ."%', 5, 0)
      + if(customer_email like '%". database::input($query) ."%', 5, 0)
      + if(customer_tax_id like '%". database::input($query) ."%', 5, 0)
      + if(concat(billing_company, ' ', billing_firstname, ' ', billing_lastname, ' ', billing_address1, ' ', billing_address2, ' ', billing_postcode, ' ', billing_city) like '%". database::input($query) ."%', 5, 0)
      + if(concat(shipping_company, ' ', shipping_firstname, ' ', shipping_lastname, ' ', shipping_address1, ' ', shipping_address2, ' ', shipping_postcode, ' ', shipping_city) like '%". database::input($query) ."%', 5, 0)
      + if(shipping_tracking_id like '%". database::input($query) ."%', 5, 0)
      + if(payment_transaction_id like '%". database::input($query) ."%', 5, 0)
    ) as relevance
    from ". DB_TABLE_PREFIX ."orders
    having relevance > 0
    order by relevance desc, id desc
    limit 5;"
  );

  if (!database::num_rows($orders_query)) return;

  while ($order = database::fetch($orders_query)) {
    $result['results'][] = [
      'id' => $order['id'],
      'title' => language::translate('title_order', 'Order') .' '. $order['id'],
      'description' => $order['customer_name'],
      'link' => document::ilink($app.'/edit_order', ['order_id' => $order['id']]),
    ];
  }

  return [$result];
