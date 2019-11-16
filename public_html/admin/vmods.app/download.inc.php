<?php

  try {
    if (empty($_GET['vmod'])) throw new Exception(language::translate('error_must_provide_vmod', 'You must provide a vMmod'));

    $file = FS_DIR_APP . 'vmods/' . basename($_GET['vmod']);

    if (!is_file($file)) throw new Exception(language::translate('error_file_could_not_be_found', 'The file could not be found'));

    header('Cache-Control: must-revalidate');
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='. preg_replace('#\.disabled$#', '.xml', basename($_GET['vmod'])));
    header('Content-Length: ' . filesize($file));
    header('Expires: 0');

    ob_end_clean();
    readfile(FS_DIR_APP . 'vmods/' . basename($_GET['vmod']));
    exit;

  } catch (Exception $e) {
    notices::add('errors', $e->getMessage());
  }
