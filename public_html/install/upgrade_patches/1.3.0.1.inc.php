<?php
  
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_HTTP_ROOT .'vqmods/logs/*',
    FS_DIR_HTTP_ROOT . WS_DIR_ADMIN .'vqmods.app/log.inc.php',
  );
  
  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

?>