<?php
  $app_config = array(
    'name' => $system->language->translate('title_modules', 'Modules'),
    'default' => 'shipping',
    'icon' => 'icon.png',
    'menu' => array(
      array(
        'title' => $system->language->translate('title_shipping', 'Shipping'),
        'doc' => 'shipping',
      ),
      array(
        'title' => $system->language->translate('title_payment', 'Payment'),
        'doc' => 'payment',
      ),
      array(
        'title' => $system->language->translate('title_order_action', 'Order Action'),
        'doc' => 'order_action',
      ),
      array(
        'title' => $system->language->translate('title_order_total', 'Order Total'),
        'doc' => 'order_total',
      ),
      array(
        'title' => $system->language->translate('title_order_success', 'Order Success'),
        'doc' => 'order_success',
      ),
      array(
        'title' => $system->language->translate('title_get_address', 'Get Address'),
        'doc' => 'get_address',
      ),
      array(
        'title' => $system->language->translate('title_background_jobs', 'Background Jobs'),
        'doc' => 'jobs',
      ),
    ),
    'docs' => array(
      'shipping' => 'modules.inc.php',
      'payment' => 'modules.inc.php',
      'order_action' => 'modules.inc.php',
      'order_total' => 'modules.inc.php',
      'order_success' => 'modules.inc.php',
      'get_address' => 'modules.inc.php',
      'jobs' => 'modules.inc.php',
      'edit_shipping' => 'edit_module.inc.php',
      'edit_payment' => 'edit_module.inc.php',
      'edit_order_total' => 'edit_module.inc.php',
      'edit_order_success' => 'edit_module.inc.php',
      'edit_order_action' => 'edit_module.inc.php',
      'edit_get_address' => 'edit_get_address.inc.php',
      'edit_job' => 'edit_job.inc.php',
    ),
  );
?>