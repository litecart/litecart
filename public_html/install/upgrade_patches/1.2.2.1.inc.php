<?php
  $modified_files = [
    [
      'file'    => FS_DIR_APP . '.htaccess',
      'search'  => "864000",
      'replace' => "86400",
    ],
  ];

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }

  if (!file_exists(FS_DIR_STORAGE . 'data/blacklist.txt')) file_xcopy(FS_DIR_APP . 'install/data/default/public_html/data/blacklist.txt', FS_DIR_STORAGE . 'data/blacklist.txt');
  if (!file_exists(FS_DIR_STORAGE . 'data/whitelist.txt')) file_xcopy(FS_DIR_APP . 'install/data/default/public_html/data/whitelist.txt', FS_DIR_STORAGE . 'data/whitelist.txt');
