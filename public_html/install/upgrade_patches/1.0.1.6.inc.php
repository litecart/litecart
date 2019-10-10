<?php
  $deleted_files = array(
    FS_DIR_APP . 'includes/modules/jobs/job_currency_updater.inc.php',
    FS_DIR_APP . 'includes/modules/shipping/sm_flat_rate.inc',
    FS_DIR_APP . 'includes/modules/shipping/sm_weight_table.inc',
    FS_DIR_APP . 'includes/modules/shipping/sm_zone.inc',
    FS_DIR_APP . 'includes/templates/templates/default.catalog/styles/loader.css.php',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
