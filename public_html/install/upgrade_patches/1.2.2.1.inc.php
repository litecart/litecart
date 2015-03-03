<?php
  $modified_files = array(
    array(
      'file'    => FS_DIR_HTTP_ROOT . '.htaccess',
      'search'  => "864000",
      'replace' => "86400",
    ),
  );
  
  foreach ($modified_files as $modification) {
    if (!file_modify($modification['file'], $modification['search'], $modification['replace'])) {
      die('<span class="error">[Error]</span></p>');
    }
  }
?>