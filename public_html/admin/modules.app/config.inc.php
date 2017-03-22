<?php

  $app_config = array(
    'name' => language::translate('title_modules', 'Modules'),
    'default' => 'jobs',
    'theme' => array(
      'color' => '#c78dc8',
      'icon' => 'fa-cube',
    ),
    'menu' => array(
      array(
        'title' => language::translate('title_customer_modules', 'Customer Modules'),
        'doc' => 'customer',
      ),
      array(
        'title' => language::translate('title_shipping_modules', 'Shipping Modules'),
        'doc' => 'shipping',
      ),
      array(
        'title' => language::translate('title_payment_modules', 'Payment Modules'),
        'doc' => 'payment',
      ),
      array(
        'title' => language::translate('title_order_modules', 'Order Modules'),
        'doc' => 'order',
      ),
      array(
        'title' => language::translate('title_order_total_modules', 'Order Total Modules'),
        'doc' => 'order_total',
      ),
      array(
        'title' => language::translate('title_job_modules', 'Job Modules'),
        'doc' => 'jobs',
      ),
    ),
    'docs' => array(
      'customer' => 'modules.inc.php',
      'order' => 'modules.inc.php',
      'order_total' => 'modules.inc.php',
      'payment' => 'modules.inc.php',
      'shipping' => 'modules.inc.php',
      'jobs' => 'modules.inc.php',
      'edit_customer' => 'edit_module.inc.php',
      'edit_job' => 'edit_module.inc.php',
      'edit_order' => 'edit_module.inc.php',
      'edit_order_total' => 'edit_module.inc.php',
      'edit_payment' => 'edit_module.inc.php',
      'edit_shipping' => 'edit_module.inc.php',
      'run_job' => 'run_job.inc.php',
    ),
  );
