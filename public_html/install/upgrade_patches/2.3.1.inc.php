<?php

// Delete old files
  $deleted_files = [
    FS_DIR_APP . 'includes/templates/default.catalog/views/listing_product_column.inc.php',
    FS_DIR_APP . 'includes/templates/default.catalog/views/listing_product_row.inc.php',
  ];

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      echo '<span class="error">[Skipped]</span></p>';
    }
  }
