<?php
  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "864000",
      'replace' => "86400",
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }

  if (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'blacklist.txt')) file_xcopy(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'install/data/default/public_html/data/blacklist.txt', FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'blacklist.txt');
  if (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'whitelist.txt')) file_xcopy(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . 'install/data/default/public_html/data/whitelist.txt', FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'whitelist.txt');
