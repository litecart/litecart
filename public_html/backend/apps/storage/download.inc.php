<?php
	if (empty($_GET['path'])) die('Missing path');

	$_GET['path'] = '/' . f::file_resolve_path($_GET['path']);

	try {

		$realfile = f::file_realpath('storage://' . ltrim($_GET['path'], '/'));
		$filename = basename($realfile);

		if (!file_exists($realfile)) {
				throw new Exception('No does not exist');
			}

		if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $realfile)) {
			throw new Exception(t('error_access_forbidden', 'Access forbiden'));
		}

		if (is_dir($realfile)) {

			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($realfile),
				RecursiveIteratorIterator::LEAVES_ONLY
			);

			$zip_file = f::file_create_tempfile();

			if (version_compare(PHP_VERSION, '8.3.0', '>=')) {
				unlink($zip_file); // Fix PHP Deprecation notice: Using empty file as ZipArchive is deprecated
			}

			$zip = new ZipArchive();

			if ($zip->open($zip_file, ZipArchive::CREATE)) {
				foreach ($files as $name => $file) {
					if (in_array(basename($file), ['.', '..'])) continue;
					$file = str_replace('\\', '/', $file);

					$zip->addFile($file, preg_replace('#^('. preg_quote(dirname($realfile) . '/', '#') .')#', '', $file));
				}
			}

			$zip->close();

			$realfile = $zip_file;
			$filename .= '.zip';
		}

		header('Cache-Control: must-revalidate');
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='. $filename);
		header('Content-Length: ' . f::file_size($realfile));
		header('Expires: 0');

		ob_end_clean();
		readfile($realfile);
		exit;

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
	}
