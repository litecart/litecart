<?php

// Delete old files
  $deleted_files = array(
    FS_DIR_APP . 'includes/modules/customers/cm_local_database.inc.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      echo '<span class="error">[Skipped]</span></p>';
    }
  }
