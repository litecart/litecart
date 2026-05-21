<?php

	function captcha_draw(string $id='default', array $config=[], string|array $attributes=''): string|bool {

		$config = [
			'width' => $config['width'] ?? 100,
			'height' => $config['height'] ?? 40,
			'length' => $config['length'] ?? 4,
			'set' => $config['set'] ?? 'numbers',
			'font' => $config['font'] ?? FS_DIR_APP . 'assets/fonts/captcha.ttf',
			'font_size' => ($config['height'] ?? 40) * 0.7,
		];

		$possible = match($config['set']) {
			'alphabet' => 'abcdefghijklmnopqrstuvwxyz',
			'numbers' => '1234567890',
			default => throw new Error('Unknown captcha set'),
		};

		$code = '';
		for ($i=0; $i<$config['length']; $i++) {
			$code .= substr($possible, random_int(0, strlen($possible) -1), 1);
		}

		if (!$image = imagecreate($config['width'], $config['height'])) {
			throw new Error('Cannot initialize new GD image stream');
		}

		// Set colors
		$background_color = imagecolorallocate($image, 255, 255, 255);
		$text_color = imagecolorallocate($image, 20, 40, 100);
		$noise_color = imagecolorallocate($image, 100, 120, 180);

		// Generate random dots in background
		for ($i=0; $i<($config['width'] * $config['height']) / 3; $i++) {
			imagefilledellipse($image, mt_rand(0, $config['width']), mt_rand(0, $config['height']), 1, 1, $noise_color);
		}

		// Generate random lines in background
		for ($i=0; $i<($config['width'] * $config['height']) / 150; $i++) {
			imageline($image, mt_rand(0, $config['width']), mt_rand(0, $config['height']), mt_rand(0, $config['width']), mt_rand(0, $config['height']), $noise_color);
		}

		// Create textbox and add text
		if (($textbox = imagettfbbox($config['font_size'], 0, $config['font'], $code)) === false) return false;

		$x = round(($config['width'] - $textbox[4]) / 2);
		$y = round(($config['height'] - $textbox[5]) / 2);
		imagettftext($image, $config['font_size'], 0, $x, $y, $text_color, $config['font'], $code) or die('Error in imagettftext function');

		// Generate base64-encoded image data
		ob_start();
		imagejpeg($image);
		$base64_image = base64_encode(ob_get_clean());

		// Free memory
		if (PHP_VERSION < '8.0.0') {
			imagedestroy($image);
		}

		// Remove expired captchas
		if (isset(session::$data['security']['captcha']) && is_array(session::$data['security']['captcha'])) {
			foreach (session::$data['security']['captcha'] as $key => $captcha) {
				if ($captcha['expires'] > date('Y-m-d H:i:s')) continue;
				unset(session::$data['security']['captcha'][$key]);
			}
		}

		// Set captcha value to session
		session::$data['security']['captcha'][$id] = [
			'value' => $code,
			'expires' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
		];

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		// Output key and image
		return implode(PHP_EOL, [
			'<div class="input-group" style="width: '. ((int)$config['width'] * 2) .'px;">',
			'  <input type="hidden" name="lc-captcha-id" value="'. f::escape_attr($id) .'">',
			'  <img src="data:image/gif;base64,'. $base64_image .'" alt="" style="width: '. $config['width'] .'px; height: '. $config['height'] .'px; border-radius: var(--border-radius) var(--border-radius) 0 0;">',
			'  ' . form_input_text('lc-captcha-response', '', ['required' => '', 'maxlength' => (int)$config['length'], 'autocomplete' => 'off', 'style' => 'font-size: '. round($config['font_size']) .'px; padding: 0; text-align: center;', ...$attributes]),
			'</div>',
		]);
	}

	function captcha_validate(string $id='default'): bool {
		try {

			if (!isset(session::$data['security']['captcha'][$id]['expires'])) {
				throw new Exception('CAPTCHA ID not found in session');
			}

			if (session::$data['security']['captcha'][$id]['expires'] < date('Y-m-d H:i:s')) {
				throw new Exception('CAPTCHA has expired');
			}

			if (empty(session::$data['security']['captcha'][$id]['value'])) {
				throw new Exception('CAPTCHA value not found in session');
			}

			if (empty($_POST['lc-captcha-response'])) {
				throw new Exception('CAPTCHA response not found in POST data');
			}

			if ($_POST['lc-captcha-response'] != session::$data['security']['captcha'][$id]['value']) {
				throw new Exception('CAPTCHA validation failed');
			}

			return true;

		} catch (Exception $e) {
			return false;

		} finally {
			// Remove the captcha from session in any case to prevent reuse
			unset(session::$data['security']['captcha'][$id]);
		}
	}
