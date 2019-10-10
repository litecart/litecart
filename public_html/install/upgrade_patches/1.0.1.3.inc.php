<?php
  $deleted_files = array(
    FS_DIR_APP . 'ext/jquery/jquery-1.9.1.min.js',
    FS_DIR_APP . 'ext/jquery/jquery-migrate-1.1.1.min.js',
    FS_DIR_APP . 'includes/functions/functions/error.inc.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
