<?php
  $deleted_files = array(
    FS_DIR_APP . 'ext/jquery/jquery-1.10.2.min.js',
    FS_DIR_APP . 'ext/jquery/jquery-1.10.2.min.map',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
