<?php
  
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmods/logs/*',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'vqmods.app/log.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.11.2.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.11.2.min.map',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/styles/ie.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/styles/ie8.css',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES .'default.admin/styles/ie9.css',
  );
  
  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

?>