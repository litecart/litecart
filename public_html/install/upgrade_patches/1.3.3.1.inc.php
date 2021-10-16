<?php

  perform_action('delete', [
    FS_DIR_APP . 'ext/sceditor/',
    FS_DIR_ADMIN . 'addons.widget/addons.cache',
    FS_DIR_ADMIN . 'discussions.widget/discussions.cache',
  ]);