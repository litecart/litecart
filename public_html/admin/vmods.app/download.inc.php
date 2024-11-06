<?php

  try {

    if (empty($_GET['vmod_id'])) {
      throw new Exception(language::translate('error_must_provide_vmod', 'You must provide a vMod'));
    }

    if (!is_file($file = FS_DIR_STORAGE . 'vmods/' . basename($_GET['vmod_id']) . '.xml')) {
      if (!is_file($file = FS_DIR_STORAGE . 'vmods/' . basename($_GET['vmod_id']) . '.disabled')) {
        throw new Exception(language::translate('error_file_not_found', 'The file could not be found'));
      }
    }

    header('Cache-Control: must-revalidate');
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='. preg_replace('#\.disabled$#', '.xml', basename($_GET['vmod'])));
    header('Content-Length: ' . filesize($file));
    header('Expires: 0');

    ob_end_clean();
    readfile(FS_DIR_STORAGE . 'vmods/' . basename($file));
    exit;

  } catch (Exception $e) {
    notices::add('errors', $e->getMessage());
  }
