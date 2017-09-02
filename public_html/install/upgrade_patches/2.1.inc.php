<?php
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'responsiveslides/',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
