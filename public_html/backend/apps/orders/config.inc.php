<?php

  return $app_config = [

    'name' => language::translate('title_orders', 'Orders'),
    'default' => 'orders',
    'priority' => 0,

    'theme' => [
      'color' => '#8fc722',
      'icon' => 'fa-shopping-cart',
    ],

    'menu' => [
      [
        'title' => language::translate('title_orders', 'Orders'),
        'doc' => 'orders',
        'params' => [],
      ],
      [
        'title' => language::translate('title_order_statuses', 'Order Statuses'),
        'doc' => 'order_statuses',
        'params' => [],
      ],
      [
        'title' => language::translate('title_shopping_carts', 'Shopping Carts'),
        'doc' => 'shopping_carts',
        'params' => [],
      ],
    ],

    'docs' => [
      'orders' => 'orders.inc.php',
      'edit_order' => 'edit_order.inc.php',
      'order_statuses' => 'order_statuses.inc.php',
      'edit_order_status' => 'edit_order_status.inc.php',
      'add_product' => 'add_product.inc.php',
      'shopping_carts' => 'shopping_carts.inc.php',
      'edit_shopping_cart' => 'edit_shopping_cart.inc.php',
    ],

    'search' => function ($query) {

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
          + if(concat(customer_company, ' ', customer_firstname, ' ', customer_lastname, ' ', customer_address1, ' ', customer_address2, ' ', customer_postcode, ' ', customer_city) like '%". database::input($query) ."%', 5, 0)
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
          'link' => document::ilink(__APP__.'/edit_order', ['order_id' => $order['id']]),
        ];
      }

      return [$result];
    },
  ];
