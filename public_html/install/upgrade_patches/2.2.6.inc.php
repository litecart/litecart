<?php

// Delete old files
  $deleted_files = array(
    FS_DIR_APP . 'ext/jquery/jquery-3.4.1.min.js',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      echo '<span class="error">[Skip]</span></p>';
    }
  }