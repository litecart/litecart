<?php
  $deleted_files = array(
    FS_DIR_APP . 'includes/functions/[^func_]*.inc.php',
    FS_DIR_APP . 'includes/classes/customer.inc.php',
    FS_DIR_APP . 'includes/classes/jobs.inc.php',
    FS_DIR_APP . 'includes/classes/order_action.inc.php',
    FS_DIR_APP . 'includes/classes/order_success.inc.php',
    FS_DIR_APP . 'includes/classes/order_total.inc.php',
    FS_DIR_APP . 'includes/classes/payment.inc.php',
    FS_DIR_APP . 'includes/classes/shipping.inc.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
