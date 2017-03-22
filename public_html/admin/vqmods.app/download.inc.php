<?php

  if (!empty($_GET['vqmod'])) {
    header('Content-Disposition: application/octet-stream');
    header('Content-Disposition: attachment; filename='. basename($_GET['vqmod']));

    $file = FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. basename($_GET['vqmod']);
    if (is_file($file)) {
      ob_end_clean();
      echo file_get_contents(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. basename($_GET['vqmod']));
      exit;
    }
  }

  echo 'File not found';
