<?php
	include '../../../../system/app_header.inc.php';

	try {

		if (empty(user::$data['id'])) {
			throw new Exception('Not logged in');
		}

		$upload_directory = 'storage://images/uploads/';

		if (!empty($_FILES['fileToUpload']) && is_uploaded_file($_FILES['fileToUpload']['tmp_name'])) {
			if (!is_dir($upload_directory)) mkdir($upload_directory, 0777);

			$image = new ent_image($_FILES['fileToUpload']['tmp_name']);

			$i=0; $filename='';

			while (empty($filename) || is_file($upload_directory . $filename)) {
				$filename = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_FILENAME) . (!empty($i) ? '_'.$i : '') .'.'. $image->type();
				$i++;
			}

			if (settings::get('image_downsample_size')) {
				list($width, $height) = explode(',', settings::get('image_downsample_size'));
				$image->resample($width, $height, 'FIT_ONLY_BIGGER');
			}

			$image->write($upload_directory . $filename, '', 90);

			unlink($_FILES['fileToUpload']['tmp_name']);

			echo json_encode([
				'success' => true,
				'file' => preg_replace('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', '', $upload_directory . $filename),
				'message' => 'uploadSuccess',
			]);
		}

	} catch (Exception $e) {
		echo json_encode([
			'success' => false,
			'message' => 'uploadError',
		]);
	}
