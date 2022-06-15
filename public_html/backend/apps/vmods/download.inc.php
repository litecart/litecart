<?php

  try {
    if (empty($_GET['vmod'])) throw new Exception(language::translate('error_must_provide_vmod', 'You must provide a vMmod'));

    $folder = 'storage://addons/' . basename($_GET['vmod']) .'/';

    if (!$xml = simplexml_load_file($folder.'vmod.xml')) {
      throw new Exception('Failed parsing vmod.xml');
    }

    $version = !empty($xml->version) ? $xml->version : date('Y-m-d', filemtime($folder.'vmod.xml'));

  // Create temporary zip archive
    $tmp_file = tempnam(sys_get_temp_dir(), '');

    $zip = new ZipArchive();
    if ($zip->open($tmp_file, ZipArchive::OVERWRITE) !== true) { // ZipArchive::CREATE throws an error with temp files in PHP 8.
      throw new Exception('Failed creating ZIP archive');
    }

    if (!$files = functions::file_search($folder.'**')) {
      throw new Exception('No files to add to ZIP archive');
    }

    foreach ($files as $file) {
      if (is_dir($file)) continue;
      if (!$zip->addFile(functions::file_realpath($file), preg_replace('#'. preg_quote($folder, '#') .'#', '', $file))) {
        throw new Exception('Failed adding contents to ZIP archive');
      }
    }

    $zip->close();

  // Output the file
    header('Cache-Control: must-revalidate');
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='. $_GET['vmod'] .'-'. $version .'.vmod.zip');
    header('Content-Length: ' . filesize($tmp_file));
    header('Expires: 0');

    ob_end_clean();
    readfile($tmp_file);
    exit;

  } catch (Exception $e) {
    notices::add('errors', $e->getMessage());
  }
