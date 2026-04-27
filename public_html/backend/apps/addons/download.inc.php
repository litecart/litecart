<?php

try {
	if (empty($_GET['vmod'])) {
		throw new Exception(t('error_must_provide_vmod', 'You must provide a vMod'));
	}

	$vmod = new ent_addon($_GET['vmod']);

	// Create temporary zip archive
	$tmp_file = f::file_create_tempfile();

	$zip = new ZipArchive();
	if ($zip->open($tmp_file, ZipArchive::OVERWRITE) !== true) {
		// ZipArchive::CREATE throws an error with temp files in PHP 8.
		throw new Exception('Failed creating ZIP archive');
	}

	if (!($files = f::file_search($vmod->data['location'] . '**'))) {
		throw new Exception('No files to add to ZIP archive');
	}

	foreach ($files as $file) {
		if (is_dir($file)) {
			continue;
		}
		if (!$zip->addFile(f::file_realpath($file), preg_replace('#^' . preg_quote($vmod->data['location'], '#') . '#', '', $file))) {
			throw new Exception('Failed adding contents to ZIP archive');
		}
	}

	$zip->close();

	// Output the file
	header('Cache-Control: must-revalidate');
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=' . f::format_path_friendly($vmod->data['id']) . '-' . $vmod->data['version'] . '.zip');
	header('Content-Length: ' . filesize($tmp_file));
	header('Expires: 0');

	ob_end_clean();
	readfile($tmp_file);
	exit;
} catch (Exception $e) {
	notices::add('errors', $e->getMessage());
}
