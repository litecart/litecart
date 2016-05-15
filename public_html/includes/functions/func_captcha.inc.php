<?php

  function captcha_get($id='default') {

    if (!isset(session::$data['captcha'][$id]['expires']) || session::$data['captcha'][$id]['expires'] < date('Y-m-d H:i:s')) return false;
    if (empty(session::$data['captcha'][$id]['value'])) return false;

    return session::$data['captcha'][$id]['value'];
  }

  function captcha_generate($width, $height, $length=6, $id='default', $set='numbers', $parameters='') {

    $code = '';
    $font = FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'captcha.ttf';

    switch ($set) {
      case 'alphabet':
        $possible = 'abcdefghijklmnopqrstuvwxyz';
      case 'numbers':
        $possible = '1234567890';
        break;
      default:
        trigger_error('Unknown captcha set.', E_USER_ERROR);
    }

    for ($i=0; $i<$length; $i++) {
      $code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
    }

    $font_size = $height * 0.75; // font size will be 75% of the image height

    $image = imagecreate($width, $height) or trigger_error('Cannot initialize new GD image stream', E_USER_ERROR);

  // Set colors
    $background_color = imagecolorallocate($image, 255, 255, 255);
    $text_color = imagecolorallocate($image, 20, 40, 100);
    $noise_color = imagecolorallocate($image, 100, 120, 180);

  // Generate random dots in background
    for( $i=0; $i<($width*$height)/3; $i++ ) {
      imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
    }

  // Generate random lines in background
    for( $i=0; $i<($width*$height)/150; $i++ ) {
      imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
    }

  // Create textbox and add text
    $textbox = imagettfbbox($font_size, 0, $font, $code) or die('Error in imagettfbbox function');
    $x = ($width - $textbox[4])/2;
    $y = ($height - $textbox[5])/2;
    imagettftext($image, $font_size, 0, $x, $y, $text_color, $font , $code) or die('Error in imagettftext function');


  // Generate base64-encoded image data
    ob_start();
    imagejpeg($image);
    $base64_image = base64_encode(ob_get_clean());

  // Free memory
    imagedestroy($image);

  // Remove expired captchas
    if (isset(session::$data['captcha']) && is_array(session::$data['captcha'])) {
      foreach(session::$data['captcha'] as $key => $captcha) {
        if ($captcha['expires'] < date('Y-m-d H:i:s')) unset(session::$data['captcha'][$key]);
      }
    }

  // Set captcha value to session
    session::$data['captcha'][$id] = array(
      'value' => $code,
      'expires' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
    );

  // Output key and image
    return '<input type="hidden" name="captcha_id" value="'. $id .'"><img alt="" src="data:image/gif;base64,'. $base64_image .'" width="'. $width .'" height="'. $height .'"'. (($parameters) ? ' ' . $parameters : '') .' />';
  }

?>