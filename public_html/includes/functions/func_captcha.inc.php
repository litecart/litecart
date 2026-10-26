<?php

  function captcha_get($id='default') {

    if (!isset(session::$data['captcha'][$id]['expires']) || session::$data['captcha'][$id]['expires'] < date('Y-m-d H:i:s')) return false;
    if (empty(session::$data['captcha'][$id]['value'])) return false;

    $value = session::$data['captcha'][$id]['value'];
    unset(session::$data['captcha'][$id]['value']);

    return $value;
  }

  function captcha_generate($width, $height, $length=6, $id='default', $set='numbers', $parameters='') {

    $code = '';
    $font = FS_DIR_STORAGE . 'data/captcha.ttf';

    switch ($set) {
      case 'alphabet':
        $possible = 'abcdefghijklmnopqrstuvwxyz';
        break;
      case 'numbers':
        $possible = '1234567890';
        break;
      default:
        throw new Error('Unknown captcha set.');
    }

    for ($i=0; $i<$length; $i++) {
      $code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
    }

    $font_size = round($height * 0.75); // font size will be 75% of the image height

  // Remove expired captchas
    if (isset(session::$data['captcha']) && is_array(session::$data['captcha'])) {
      foreach (session::$data['captcha'] as $key => $captcha) {
        if (empty($captcha['expires']) || $captcha['expires'] < date('Y-m-d H:i:s')) unset(session::$data['captcha'][$key]);
      }
    }

  // Set captcha value to session, before rendering so a rendering failure still leaves a verifiable answer
    session::$data['captcha'][$id] = [
      'value' => $code,
      'expires' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
    ];

  // GD is required to render an image at all, without it the form cannot be verified
    if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
      trigger_error('The GD extension is required to render the CAPTCHA, the form cannot be submitted until it is available.', E_USER_WARNING);
      return implode (PHP_EOL, [
        '<input type="hidden" name="captcha_id" value="'. functions::escape_attr($id) .'">',
        '<div class="alert alert-warning"'. (($parameters) ? ' ' . $parameters : '') .'>',
        '  '. functions::draw_fonticon('fa-exclamation-triangle') .' '. language::translate('captcha_unavailable', 'The CAPTCHA is currently unavailable, so this form cannot be submitted.'),
        '</div>'
      ]);
    }

  // Create the image
    if (!$image = imagecreatetruecolor($width, $height)) {
      throw new Error('Cannot initialize new GD image stream');
    }

  // Set colors
    $background_color = imagecolorallocate($image, 255, 255, 255);
    $text_color = imagecolorallocate($image, 20, 40, 100);
    $noise_color = imagecolorallocate($image, 100, 120, 180);

  // A truecolor image starts out black, so the background has to be painted
    imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

  // Generate random dots in background
    for( $i=0; $i<($width*$height)/3; $i++ ) {
      imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
    }

  // Generate random lines in background
    for( $i=0; $i<($width*$height)/150; $i++ ) {
      imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
    }

  // Render with FreeType when the font file can be read
    $drawn = false;

    if (function_exists('imagettfbbox') && function_exists('imagettftext') && is_readable($font)) {

      $textbox = imagettfbbox($font_size, 0, $font, $code);

      if ($textbox !== false) {

        $x = round(($width - $textbox[4]) / 2);
        $y = round(($height - $textbox[5]) / 2);

        $drawn = (bool)imagettftext($image, $font_size, 0, $x, $y, $text_color, $font, $code);
      }
    }

  // Draw the code with the built-in font, which needs neither FreeType nor a font file
    if (!$drawn) {
      trigger_error('The CAPTCHA is rendered with the built-in font, FreeType or the font file '. $font .' is not available.', E_USER_WARNING);

      $builtin_font = 5;
      for ($i = 5; $i > 1; $i--) {
        if (imagefontwidth($i) * strlen($code) <= $width && imagefontheight($i) <= $height) {
          $builtin_font = $i;
          break;
        }
      }

      $char_width = imagefontwidth($builtin_font);
      $char_height = imagefontheight($builtin_font);
      $x = max(0, (int)round(($width - $char_width * strlen($code)) / 2));
      $y = max(0, (int)round(($height - $char_height) / 2));

      for ($i = 0; $i < strlen($code); $i++) {
        $offset = max(0, min($height - $char_height, $y + mt_rand(-2, 2)));
        imagestring($image, $builtin_font, $x + ($i * $char_width), $offset, $code[$i], $text_color);
      }
    }

  // Generate base64-encoded image data
    ob_start();
    imagejpeg($image);
    $base64_image = base64_encode(ob_get_clean());

  // Free memory
    if (PHP_VERSION_ID < 80000) {
      imagedestroy($image);
    }

  // Output key and image
    return implode(PHP_EOL, [
      '<input type="hidden" name="captcha_id" value="'. functions::escape_attr($id) .'">',
      '<img src="data:image/jpeg;base64,'. $base64_image .'" alt="" style="width: '. (int)$width .'px; height: '. (int)$height .'px"'. (($parameters) ? ' ' . $parameters : '') .'>',
    ]);
  }
