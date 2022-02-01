<?php

  function image_scale_by_width($width, $ratio) {
    switch($ratio) {
      case '2:3':
        return [$width, round($width/2*3)];
      case '3:2':
        return [$width, round($width/3*2)];
      case '3:4':
        return [$width, round($width/3*4)];
      case '4:3':
        return [$width, round($width/4*3)];
      case '16:9':
        return [$width, round($width/16*9)];
      case '1:1':
      default:
        return [$width, $width];
    }
  }

  function image_process($source, $options) {

    try {

      if (!is_file($source)) $source = FS_DIR_STORAGE . 'images/no_image.png';

      $options = [
        'destination' => fallback($options['destination'], FS_DIR_STORAGE . 'cache/'),
        'width' => fallback($options['width'], 0),
        'height' => fallback($options['height'], 0),
        'clipping' => fallback($options['clipping'], 'FIT_ONLY_BIGGER'),
        'quality' => fallback($options['quality'], settings::get('image_quality')),
        'trim' => fallback($options['trim'], false),
        'interlaced' => !empty($options['interlaced']) ? true : false,
        'overwrite' => fallback($options['overwrite'], false),
        'watermark' => fallback($options['watermark'], false),
      ];

    // If destination is a directory
      if (is_dir($options['destination'])) {
        $options['destination'] = rtrim($options['destination'], '/') .'/'. basename($source);
      }

      if (is_file($options['destination'])) {
        if (filemtime($options['destination']) >= filemtime($source)) {
          return functions::image_path($options['destination']);
        } else {
          unlink($options['destination']);
        }
      }

    // Return an already existing file
      if (is_file($options['destination'])) {
        if (!empty($options['overwrite'])) {
          unlink($options['destination']);
        } else {
          return functions::image_path($options['destination']);
        }
      }

      $image = new ent_image($source);

      if (!empty($options['trim'])) {
        $image->trim();
      }

      if ($options['width'] > 0 || $options['height'] > 0) {
        if (!$image->resample($options['width'], $options['height'], strtoupper($options['clipping']))) return;
      }

      if (!empty($options['watermark'])) {
        if ($options['watermark'] === true) $options['watermark'] = FS_DIR_STORAGE . 'images/logotype.png';
        if (!$image->watermark($options['watermark'], 'RIGHT', 'BOTTOM')) return;
      }

      if (!$image->write($options['destination'], $options['quality'], !empty($options['interlaced']))) return;

      return functions::image_path($options['destination']);

    } catch (Exception $e) {
      trigger_error('Could not process image: ' . $e->getMessage(), E_USER_WARNING);
    }
  }

  function image_resample($source, $destination, $width=0, $height=0, $clipping='FIT_ONLY_BIGGER', $quality=null) {

    return image_process($source, [
      'destination' => $destination,
      'width' => $width,
      'height' => $height,
      'clipping' => $clipping,
      'trim' => false,
      'quality' => $quality,
    ]);
  }

  function image_thumbnail($source, $width=0, $height=0, $clipping='FIT_ONLY_BIGGER', $trim=false) {

    if (!is_file($source)) $source = FS_DIR_STORAGE . 'images/no_image.png';

    $path = functions::image_path($source);

    if (pathinfo($source, PATHINFO_EXTENSION) == 'svg') {
      return $path;
    }

    if (isset($_SERVER['HTTP_ACCEPT']) && preg_match('#image/webp#', $_SERVER['HTTP_ACCEPT'])) {
      $extension = 'webp';
    } else {
      $extension = pathinfo($source, PATHINFO_EXTENSION);
    }

    switch (strtoupper($clipping)) {

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
        trigger_error("Unknown image clipping method ($clipping)", E_USER_WARNING);
        return;
    }

    $filename = implode([
      sha1($path),
      $trim ? '_t' : null,
      '_'.(int)$width .'x'. (int)$height,
      $clipping_filename_flag,
      settings::get('image_thumbnail_interlaced') ? '_i' : null,
      '.'.$extension,
    ]);

    $cache_file = FS_DIR_STORAGE . 'cache/' . substr($filename, 0, 2) . '/' . $filename;

    if (is_file($cache_file)) {
      if (filemtime($cache_file) >= filemtime($source)) {
        return functions::image_path($cache_file);
      } else {
        functions::image_delete_cache($source);
      }
    }

    if (!is_dir(FS_DIR_STORAGE . 'cache/' . substr($filename, 0, 2))) {
      if (!mkdir(FS_DIR_STORAGE . 'cache/' . substr($filename, 0, 2))) {
        trigger_error('Could not create cache subfolder', E_USER_WARNING);
        return false;
      }
    }

    return image_process($source, [
      'destination' => $cache_file,
      'width' => $width,
      'height' => $height,
      'clipping' => $clipping,
      'trim' => fallback($trim, false),
      'quality' => settings::get('image_thumbnail_quality'),
      'interlaced' => settings::get('image_thumbnail_interlaced'),
    ]);
  }

  function image_path($file) {
    return preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .'|'. preg_quote(DOCUMENT_ROOT, '#') .')#', '', functions::file_realpath($file));
  }

  function image_delete_cache($file) {

    $path = functions::image_path($file);

    $cache_name = sha1($path);

    foreach (glob(FS_DIR_STORAGE . 'cache/'. substr($cache_name, 0, 2) .'/' . $cache_name .'*') as $file) {
      unlink($file);
    }
  }
