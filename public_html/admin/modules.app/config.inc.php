<?php

$app_config = array(
  'name' => $system->language->translate('title_modules', 'Modules'),
  'index' => 'modules.php',
  'params' => array(
    'type' => 'shipping'
  ),
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'name' => $system->language->translate('title_shipping', 'Shipping'),
      'link' => 'modules.php',
      'params' => array('type' => 'shipping'),
    ),
    array(
      'name' => $system->language->translate('title_payment', 'Payment'),
      'link' => 'modules.php',
      'params' => array('type' => 'payment'),
    ),
    array(
      'name' => $system->language->translate('title_order_action', 'Order Action'),
      'link' => 'modules.php',
      'params' => array('type' => 'order_action'),
    ),
    array(
      'name' => $system->language->translate('title_order_total', 'Order Total'),
      'link' => 'modules.php',
      'params' => array('type' => 'order_total'),
    ),
    array(
      'name' => $system->language->translate('title_order_success', 'Order Success'),
      'link' => 'modules.php',
      'params' => array('type' => 'order_success'),
    ),
    array(
      'name' => $system->language->translate('title_get_address', 'Get Address'),
      'link' => 'modules.php',
      'params' => array('type' => 'get_address'),
    ),
    array(
      'name' => $system->language->translate('title_background_jobs', 'Background Jobs'),
      'link' => 'modules.php',
      'params' => array('type' => 'jobs'),
    ),
  ),
);

?>