<?php

  $modified_files = [
    [
      'file'    => FS_DIR_APP . '.htaccess',
      'search'  => "    SetEnv HTTP_MOD_REWRITE On",
      'replace' => "    SetEnv MOD_REWRITE On",
    ],
  ];

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      echo('<span class="error">[Error]</span><br />Could not find: '. $modification['search'] .'</p>');
    }
  }
