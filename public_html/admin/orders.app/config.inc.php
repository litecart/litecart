<?php

$app_config = array(
  'name' => $system->language->translate('title_orders', 'Orders'),
  'index' => 'orders.php',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'name' => $system->language->translate('title_orders', 'Orders'),
      'link' => 'orders.php'
    ),
    array(
      'name' => $system->language->translate('title_order_statuses', 'Order Statuses'),
      'link' => 'order_statuses.php'
    ),
  ),
);

?>