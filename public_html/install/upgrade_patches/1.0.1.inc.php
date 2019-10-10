<?php
  $deleted_files = array(
    FS_DIR_APP . 'includes/modules/get_address/',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
