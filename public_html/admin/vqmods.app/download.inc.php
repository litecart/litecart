<?php

  try {
    if (empty($_GET['vqmod'])) throw new Exception(language::translate('error_must_provide_vqmod', 'You must provide a vQmod'));

    $file = FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. basename($_GET['vqmod']);

    if (!is_file($file)) throw new Exception(language::translate('error_file_could_not_be_found', 'The file could not be found'));

    header('Cache-Control: must-revalidate');
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='. preg_replace('#\.disabled$#', '.xml', basename($_GET['vqmod'])));
    header('Content-Length: ' . filesize($file));
    header('Expires: 0');

    ob_end_clean();
    readfile(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'vqmod/xml/'. basename($_GET['vqmod']));
    exit;

  } catch (Exception $e) {
    notices::add('errors', $e->getMessage());
  }
