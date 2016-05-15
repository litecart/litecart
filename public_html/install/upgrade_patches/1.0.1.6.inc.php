<?php
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'jobs/job_currency_updater.inc.php',
    FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/sm_flat_rate.inc',
    FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/sm_weight_table.inc',
    FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/sm_zone.inc',
    FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . 'templates/default.catalog/styles/loader.css.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
?>