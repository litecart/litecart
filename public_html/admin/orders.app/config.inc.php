<?php

  $app_config = array(
    'name' => language::translate('title_orders', 'Orders'),
    'default' => 'orders',
    'icon' => 'icon.png',
    'menu' => array(
      array(
        'title' => language::translate('title_orders', 'Orders'),
        'doc' => 'orders',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_order_statuses', 'Order Statuses'),
        'doc' => 'order_statuses',
        'params' => array(),
      ),
    ),
    'docs' => array(
      'orders' => 'orders.inc.php',
      'edit_order' => 'edit_order.inc.php',
      'order_statuses' => 'order_statuses.inc.php',
      'edit_order_status' => 'edit_order_status.inc.php',
      'add_product' => 'add_product.inc.php',
      'add_custom_item' => 'add_custom_item.inc.php',
      'get_address.json' => 'get_address.json.inc.php',
    ),
  );

?>