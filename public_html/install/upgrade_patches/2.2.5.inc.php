<?php

// Delete old files
  $deleted_files = array(
    FS_DIR_ADMIN . 'orders.app/printable_order_copy.inc.php',
    FS_DIR_ADMIN . 'orders.app/printable_packing_slip.inc.php',
    FS_DIR_ADMIN . 'includes/modules/customer/cm_google_maps.php',
    FS_DIR_APP . 'includes/modules/customer/cm_google_maps.php',
    FS_DIR_APP . 'includes/modules/customer/cm_local_database.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      echo '<span class="error">[Skip]</span></p>';
    }
  }
