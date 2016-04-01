<?php

  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "    Header unset Last-Modified" . PHP_EOL,
      'replace' => "",
    ),
    array(
      'file'    => FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . '.htaccess',
      'search'  => "  <FilesMatch \"\\.(gif|ico|jpg|jpeg|js|pdf|png|ttf)$\">" . PHP_EOL
                 . "    Header set Cache-Control \"max-age=86400, public, must-revalidate\"",
      'replace' => "  <FilesMatch \"\\.(gif|ico|jpg|jpeg|js|pdf|png|ttf)$\">" . PHP_EOL
                 . "    Header set Cache-Control \"max-age=604800, public, must-revalidate\"",
    ),
  );

  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }

?>