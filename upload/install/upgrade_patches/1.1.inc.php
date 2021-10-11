<?php

  perform_action('delete', [
    FS_DIR_APP . 'includes/functions/[^func_]*.inc.php',
    FS_DIR_APP . 'includes/classes/customer.inc.php',
    FS_DIR_APP . 'includes/classes/jobs.inc.php',
    FS_DIR_APP . 'includes/classes/order_action.inc.php',
    FS_DIR_APP . 'includes/classes/order_success.inc.php',
    FS_DIR_APP . 'includes/classes/order_total.inc.php',
    FS_DIR_APP . 'includes/classes/payment.inc.php',
    FS_DIR_APP . 'includes/classes/shipping.inc.php',
  ]);
