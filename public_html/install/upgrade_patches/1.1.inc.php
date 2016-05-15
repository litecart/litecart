<?php
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_FUNCTIONS . '[^func_]*.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'customer.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'jobs.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'order_action.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'order_success.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'order_total.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'payment.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'shipping.inc.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
?>