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

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.12.0.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery-1.12.0.min.map',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'trumbowyg/plugins/colors/ui/images/',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'trumbowyg/ui/images/',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }
