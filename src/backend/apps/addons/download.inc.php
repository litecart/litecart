<?php

	try {

		if (empty($_GET['addon_id'])) {
			throw new Exception(t('error_must_provide_an_addon', 'You must provide an add-on'));
		}

		$addon = new ent_addon($_GET['addon_id']);

		// Create temporary zip archive
		$tmp_file = f::file_create_tempfile();

		$zip = new ZipArchive();
		if ($zip->open($tmp_file, ZipArchive::OVERWRITE) !== true) { // ZipArchive::CREATE throws an error with temp files in PHP 8.
			throw new Exception('Failed creating ZIP archive');
		}

		if (empty($addon->data['location'])) {
			throw new Exception('Failed to determine addon location');
		}

		if (!$files = f::file_search($addon->data['location'].'**')) {
			throw new Exception('No files to add to ZIP archive');
		}

		foreach ($files as $file) {

			if (is_dir($file)) {
				continue;
			}

			if (!$zip->addFile(f::file_realpath($file), preg_replace('#^'. preg_quote($addon->data['location'], '#') .'#', '', $file))) {
				throw new Exception('Failed adding contents to ZIP archive');
			}
		}

		$zip->close();

		// Output the file
		header('Cache-Control: must-revalidate');
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='. f::format_path_friendly($addon->data['id']) . ($addon->data['version'] ? '-'. $addon->data['version'] : '') .'.zip');
		header('Content-Length: ' . filesize($tmp_file));
		header('Expires: 0');

		ob_end_clean();
		readfile($tmp_file);
		exit;

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
	}
