<?php

	function image_scale_by_width($width, $aspect_ratio) {
		list($x, $y) = preg_split('#[:/]#', $aspect_ratio, 2);
		return [$width, round($width / $x * $y)];
	}

	function image_scale_by_height($height, $aspect_ratio) {
		list($x, $y) = preg_split('#[:/]#', $aspect_ratio, 2);
		return [round($height / $y * $x), $height];
	}

	function image_process($source, $options) {

		try {

			$source = preg_replace('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', 'storage://', $source);
			$source = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', 'app://', $source);

			if (!is_file($source)) {
				$source = 'storage://images/no_image.svg';
			}

			$options = [
				'destination' => fallback($options['destination'], 'storage://cache/'),
				'width' => fallback($options['width'], 0),
				'height' => fallback($options['height'], 0),
				'quality' => fallback($options['quality'], settings::get('image_quality')),
				'trim' => fallback($options['trim'], false),
				'interlaced' => !empty($options['interlaced']),
				'overwrite' => fallback($options['overwrite'], false),
				'watermark' => fallback($options['watermark'], false),
				'extension' => fallback($options['extension']),
			];

			// If destination is a folder
			if (is_dir($options['destination']) || substr($options['destination'], -1) == '/') {

				// If destination is a cache folder without filename
				if (preg_match('#^'. preg_quote('storage://cache/', '#') .'$#', $options['destination'])) {

					if (!$options['extension']) {
						if (settings::get('avif_enabled') && isset($_SERVER['HTTP_ACCEPT']) && preg_match('#image/avif#', $_SERVER['HTTP_ACCEPT'])) {
							$options['extension'] = 'avif';

						} else if (settings::get('webp_enabled') && isset($_SERVER['HTTP_ACCEPT']) && preg_match('#image/webp#', $_SERVER['HTTP_ACCEPT'])) {
							$options['extension'] = 'webp';

						} else {
							$options['extension'] = pathinfo($source, PATHINFO_EXTENSION);
						}
					}

					$filename = implode([
						sha1($source),
						$options['trim'] ? '_t' : '',
						($options['width'] && $options['height']) ? '_'.(int)$options['width'] .'x'. (int)$options['height'] : '',
						$options['watermark'] ? '_wm' : '',
						settings::get('image_thumbnail_interlaced') ? '_i' : '',
						'.'.$options['extension'],
					]);

					$options['destination'] = 'storage://cache/'. substr($filename, 0, 2) . '/' . $filename;

				} else {
					$options['destination'] = rtrim($options['destination'], '/') .'/'. basename($source);
				}
			}

			// Who uses GIF these days?
			$options['destination'] = preg_replace('#\.gif$#', '.png', $options['destination']);

			// Return an already existing file
			if (is_file($options['destination'])) {
				if ($options['overwrite'] || filemtime($source) >= filemtime($options['destination'])) {
					unlink($options['destination']);
				} else {
					return $options['destination'];
				}
			}

			if (!is_dir(dirname($options['destination']))) {
				if (!mkdir(dirname($options['destination']), 0777, true)) {
					trigger_error('Could not create destination folder', E_USER_WARNING);
					return false;
				}
			}

			// Process the image
			$image = new ent_image($source);

			if (!empty($options['trim'])) {
				$image->trim();
			}

			if ($options['width'] > 0 || $options['height'] > 0) {
				if (!$image->resample($options['width'], $options['height'])) return;
			}

			if (!empty($options['watermark'])) {

				if ($options['watermark'] === true) {
					$options['watermark'] = 'storage://images/logotype.png';
				}

				if (!$image->watermark($options['watermark'], 'RIGHT', 'BOTTOM')) return;
			}

			if (!$image->save($options['destination'], $options['quality'], !empty($options['interlaced']))) return;

			return $options['destination'];

		} catch (Exception $e) {
			trigger_error('Could not process image: ' . $e->getMessage(), E_USER_WARNING);
		}
	}

	function image_aspect_ratio($width, $height) {

		$ratio = [$width, $height];

		for ($x = $ratio[1]; $x > 1; $x--) {
			if (($ratio[0] % $x) == 0 && ($ratio[1] % $x) == 0) {
				$ratio = [$ratio[0] / $x, $ratio[1] / $x];
			}
		}

		return implode('/', $ratio);
	}

	function image_resample($source, $destination, $width=0, $height=0, $quality=null) {

		return image_process($source, [
			'destination' => $destination,
			'width' => $width,
			'height' => $height,
			'trim' => false,
			'quality' => $quality,
		]);
	}

	function image_thumbnail($source, $width=0, $height=0, $trim=false) {

		if (!is_file($source)) {
			$source = 'storage://images/no_image.svg';
		}

		if (pathinfo($source, PATHINFO_EXTENSION) == 'svg') {
			return $source;
		}

		return image_process($source, [
			'width' => $width,
			'height' => $height,
			'trim' => $trim,
			'quality' => settings::get('image_thumbnail_quality'),
			'interlaced' => settings::get('image_thumbnail_interlaced'),
		]);
	}

	function image_relative_file($file) {

		$file = str_replace('\\', '/', $file);
		$file = preg_replace('#^(app://|'. preg_quote(FS_DIR_APP, '#') .')#', '', $file);
		$file = preg_replace('#^(storage://|'. preg_quote(FS_DIR_STORAGE, '#') .')#', '', $file);

		return preg_replace('#^'. preg_quote(DOCUMENT_ROOT, '#') .'#', '', $file);
	}

	function image_delete_cache($file) {

		$cache_name = sha1(image_relative_file($file));

		functions::file_delete('storage://cache/'. substr($cache_name, 0, 2) .'/' . $cache_name .'*');
	}
