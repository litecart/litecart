<?php
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'jobs/job_currency_updater.inc.php',
  );
  
  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
?>