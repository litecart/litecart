<?php

// Delete old files
  $deleted_files = [
    FS_DIR_ADMIN . '.htaccess',
    FS_DIR_ADMIN . '.htpasswd',
  ];

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      echo '<span class="error">[Skipped]</span></p>';
    }
  }
