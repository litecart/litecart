<?php

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'sceditor/',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'addons.widget/addons.cache',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'discussions.widget/discussions.cache',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
