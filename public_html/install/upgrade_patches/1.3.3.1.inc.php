<?php

  $deleted_files = array(
    FS_DIR_APP . 'ext/sceditor/',
    FS_DIR_ADMIN . 'addons.widget/addons.cache',
    FS_DIR_ADMIN . 'discussions.widget/discussions.cache',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
