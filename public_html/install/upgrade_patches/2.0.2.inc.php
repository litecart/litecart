<?php

// Delete old files
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.admin/images/home.png',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.admin/images/search.png',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'default.admin/images/scroll_up.png',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
