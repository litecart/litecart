<?php

  return $app_config = [
    'name' => language::translate('title_orders', 'Orders'),
    'default' => 'orders',
    'priority' => 0,
    'theme' => [
      'color' => '#9dd238',
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
    ],
    'docs' => [
      'orders' => 'orders.inc.php',
      'edit_order' => 'edit_order.inc.php',
      'edit_order_item' => 'edit_order_item.inc.php',
      'order_statuses' => 'order_statuses.inc.php',
      'edit_order_status' => 'edit_order_status.inc.php',
      'add_product' => 'add_product.inc.php',
      'printable_order_copy' => 'printable_order_copy.inc.php',
      'printable_packing_slip' => 'printable_packing_slip.inc.php',
      'product_picker' => 'product_picker.inc.php',
    ],
  ];
