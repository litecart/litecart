<?php

// Delete some files
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-3.2.1.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/less/framework/navigation.less',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/less/framework/navigation.less',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.catalog/less/framework/panels.less',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
