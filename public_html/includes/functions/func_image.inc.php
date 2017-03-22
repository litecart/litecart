<?php

  function image_scale_by_width($width, $ratio) {
    switch($ratio) {
      case '2:3':
        return array($width, round($width/2*3));
      case '3:2':
        return array($width, round($width/3*2));
      case '3:4':
        return array($width, round($width/3*4));
      case '4:3':
        return array($width, round($width/4*3));
      case '16:9':
        return array($width, round($width/16*9));
      case '1:1':
      default:
        return array($width, $width);
    }
  }

  function image_process($source, $options) {

    if (!is_file($source)) $source = FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png';

    $options = array(
      'destination' => !empty($options['destination']) ? $options['destination'] : FS_DIR_HTTP_ROOT . WS_DIR_CACHE,
      'width' => !empty($options['width']) ? $options['width'] : 0,
      'height' => !empty($options['height']) ? $options['height'] : 0,
      'clipping' => !empty($options['clipping']) ? $options['clipping'] : 'FIT_ONLY_BIGGER',
      'quality' => isset($options['quality']) ? $options['quality'] : settings::get('image_quality'),
      'trim' => !empty($options['trim']) ? $options['trim'] : false,
      'interlaced' => !empty($options['interlaced']) ? true : false,
      'watermark' => !empty($options['watermark']) ? $options['watermark'] : false,
      'extension' => !empty($options['extension']) ? $options['extension'] : null,
    );

  // If destination is a directory
    if (is_dir($options['destination'])) {

      $options['destination'] = rtrim($options['destination'], '/') . '/';

      switch (strtoupper($options['clipping'])) {
        case 'CROP':
          $clipping_filename_flag = '_c';
          break;
        case 'CROP_ONLY_BIGGER':
          $clipping_filename_flag = '_cob';
          break;
        case 'STRETCH':
          $clipping_filename_flag = '_s';
          break;
        case 'FIT':
          $clipping_filename_flag = '_f';
          break;
        case 'FIT_USE_WHITESPACING':
          $clipping_filename_flag = '_fwb';
          break;
        case 'FIT_ONLY_BIGGER':
          $clipping_filename_flag = '_fob';
          break;
        case 'FIT_ONLY_BIGGER_USE_WHITESPACING':
          $clipping_filename_flag = '_fobws';
          break;
        default:
          trigger_error('Unknown resample method ('.$options['clipping'].') for image', E_USER_WARNING);
          return;
      }

      $source_webpath = str_replace(str_replace('\\', '/', realpath(FS_DIR_HTTP_ROOT)), '', str_replace('\\', '/', realpath($source)));
      $options['destination'] .= implode('', array(
          sha1($source_webpath),
          !empty($options['trim']) ? '_t' : null,
          '_'.(int)$options['width'] .'x'. (int)$options['height'],
          $clipping_filename_flag,
          !empty($options['watermark']) ? '_wm' : null,
          !empty($options['interlaced']) ? '_i' : null,
          '.' . pathinfo($source, PATHINFO_EXTENSION),
      ));
    }

  // Return an already existing file
    if (is_file($options['destination'])) {
      if (!empty($options['overwrite'])) {
        unlink($options['destination']);
      } else {
        return str_replace(str_replace('\\', '/', realpath(FS_DIR_HTTP_ROOT)), '', str_replace('\\', '/', realpath($options['destination'])));
      }
    }

    if (!$image = new ctrl_image($source)) return;

    if (empty($options['extension'])) {
      $options['extension'] = $image->type();
    }

    if (!empty($options['trim'])) {
      $image->trim();
    }

    if ($options['width'] != 0 || $options['height'] != 0) {
      if (!$image->resample($options['width'], $options['height'], strtoupper($options['clipping']))) return;
    }

    if (!empty($options['watermark'])) {
      if ($options['watermark'] === true) $options['watermark'] = FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'logotype.png';
      if (!$image->watermark($options['watermark'], 'RIGHT', 'BOTTOM')) return;
    }

    switch($options['extension']) {
      case 'jpg':
        $options['extension'] = 'jpg';
        break;
      case 'gif':
      case 'png':
      case 'bmp':
        $options['extension'] = 'png';
        break;
      default:
        $options['extension'] = 'png';
        break;
    }

    if (!$image->write($options['destination'], $options['extension'], $options['quality'], !empty($options['interlaced']))) return;

    return str_replace(FS_DIR_HTTP_ROOT, '', str_replace('\\', '/', realpath($options['destination'])));
  }

  function image_resample($source, $destination, $width=0, $height=0, $clipping='FIT_ONLY_BIGGER', $quality=null) {

    return image_process($source, array(
      'destination' => $destination,
      'width' => $width,
      'height' => $height,
      'clipping' => $clipping,
      'trim' => false,
      'quality' => $quality,
    ));
  }

  function image_thumbnail($source, $width=0, $height=0, $clipping='FIT_ONLY_BIGGER', $trim=false) {

    return image_process($source, array(
      'destination' => FS_DIR_HTTP_ROOT . WS_DIR_CACHE,
      'width' => $width,
      'height' => $height,
      'clipping' => $clipping,
      'trim' => !empty($trim) ? $trim : false,
      'quality' => settings::get('image_thumbnail_quality'),
      'interlaced' => settings::get('image_thumbnail_interlaced'),
    ));
  }

  function image_delete_cache($file) {

    $webpath = str_replace(FS_DIR_HTTP_ROOT, '', $file);

    $cachename = sha1($webpath);

    $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_CACHE . $cachename .'*');

    if ($files) foreach($files as $file) {
      unlink($file);
    }
  }
