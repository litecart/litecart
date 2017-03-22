<?php

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.11.3.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.11.3.min.map',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-migrate-1.2.1.min.js',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
