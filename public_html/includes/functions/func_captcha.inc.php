<?php

	function captcha_draw($id='default', $config=[], $parameters='') {

		$config = [
			'width' => fallback($config['width'], 100),
			'height' => fallback($config['height'], 40),
			'length' => fallback($config['length'], 4),
			'set' => fallback($config['set'], 'numbers'),
			'font' => fallback($config['font'], FS_DIR_APP . 'assets/fonts/captcha.ttf'),
			'font_size' => fallback($config['height'], 40) * 0.7,
		];

		switch ($config['set']) {

			case 'alphabet':
				$possible = 'abcdefghijklmnopqrstuvwxyz';
				break;

			case 'numbers':
				$possible = '1234567890';
				break;

			default:
				throw new ErrorException('Unknown captcha set');
		}

		$code = '';
		for ($i=0; $i<$config['length']; $i++) {
			$code .= substr($possible, mt_rand(0, strlen($possible) -1), 1);
		}

		if (!$image = imagecreate($config['width'], $config['height'])) {
			throw new ErrorException('Cannot initialize new GD image stream');
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
		if (($textbox = imagettfbbox($config['font_size'], 0, $config['font'], $code)) === false) return;

		$x = round(($config['width'] - $textbox[4]) / 2);
		$y = round(($config['height'] - $textbox[5]) / 2);
		imagettftext($image, $config['font_size'], 0, $x, $y, $text_color, $config['font'], $code) or die('Error in imagettftext function');

		// Generate base64-encoded image data
		ob_start();
		imagejpeg($image);
		$base64_image = base64_encode(ob_get_clean());

		// Free memory
		imagedestroy($image);

		// Remove expired captchas
		if (isset(session::$data['lc-captcha']) && is_array(session::$data['lc-captcha'])) {
			foreach (session::$data['lc-captcha'] as $key => $captcha) {
				if ($captcha['expires'] < date('Y-m-d H:i:s')) unset(session::$data['lc-captcha'][$key]);
			}
		}

		// Set captcha value to session
		session::$data['lc-captcha'][$id] = [
			'value' => $code,
			'expires' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
		];

		// Output key and image
		return implode(PHP_EOL, [
			'<div class="input-group" style="width: '. ((int)$config['width'] * 2) .'px;">',
			'  <input type="hidden" name="lc-captcha-id" value="'. functions::escape_attr($id) .'">',
			'  <img src="data:image/gif;base64,'. $base64_image .'" alt="" style="width: '. $config['width'] .'px; height: '. $config['height'] .'px; border-radius: var(--border-radius) var(--border-radius) 0 0;">',
			'  ' . form_input_text('lc-captcha-response', '', 'required maxlength="'. (int)$config['length'] .'" autocomplete="off" style="font-size: '. round($config['font_size'])  .'px; padding: 0; text-align: center;"'. ($parameters ? ' '. $parameters : '')),
			'</div>',
		]);
	}

	function captcha_validate($id='default') {

		if (!isset(session::$data['lc-captcha'][$id]['expires']) || session::$data['lc-captcha'][$id]['expires'] < date('Y-m-d H:i:s')) {
			return false;
		}

		if (empty(session::$data['lc-captcha'][$id]['value']) || empty($_POST['lc-captcha-response']) || $_POST['lc-captcha-response'] != session::$data['lc-captcha'][$id]['value']) {
			return false;
		}

		unset(session::$data['lc-captcha'][$id]['value']);

		return true;
	}
