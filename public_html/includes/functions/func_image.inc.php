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

      if (!is_file($source)) {
        $source = 'storage://images/no_image.png';
      }

      $options = [
        'destination' => fallback($options['destination'], 'storage://cache/'),
        'width' => fallback($options['width'], 0),
        'height' => fallback($options['height'], 0),
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
          return $options['destination'];
        } else {
          unlink($options['destination']);
        }
      }

    // Return an already existing file
      if (is_file($options['destination'])) {
        if (!empty($options['overwrite'])) {
          unlink($options['destination']);
        } else {
          return $options['destination'];
        }
      }

      $image = new ent_image($source);

      if (!empty($options['trim'])) {
        $image->trim();
      }

      if ($options['width'] > 0 || $options['height'] > 0) {
        if (!$image->resample($options['width'], $options['height'])) return;
      }

      if (!empty($options['watermark'])) {
        if ($options['watermark'] === true) $options['watermark'] = 'storage://images/logotype.png';
        if (!$image->watermark($options['watermark'], 'RIGHT', 'BOTTOM')) return;
      }

      if (!$image->write($options['destination'], $options['quality'], !empty($options['interlaced']))) return;

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

    if (!is_file($source)) $source = 'storage://images/no_image.png';

    $path = functions::file_webpath($source);

    if (pathinfo($source, PATHINFO_EXTENSION) == 'svg') {
      return $path;
    }

    if (isset($_SERVER['HTTP_ACCEPT']) && preg_match('#image/webp#', $_SERVER['HTTP_ACCEPT'])) {
      $extension = 'webp';
    } else {
      $extension = pathinfo($source, PATHINFO_EXTENSION);
    }

    $filename = implode([
      sha1($path),
      $trim ? '_t' : null,
      '_'.(int)$width .'x'. (int)$height,
      settings::get('image_thumbnail_interlaced') ? '_i' : null,
      '.'.$extension,
    ]);

    $cache_directory = 'storage://cache/' . substr($filename, 0, 2) .'/';
    $cache_file = $cache_directory . $filename;

    if (is_file($cache_file)) {
      if (filemtime($cache_file) >= filemtime($source)) {
        return $cache_file;
      } else {
        functions::image_delete_cache($source);
      }
    }
    if (!is_dir($cache_directory)) {
      if (!mkdir($cache_directory)) {
        trigger_error('Could not create cache subfolder', E_USER_WARNING);
        return false;
      }
    }

    return image_process($source, [
      'destination' => $cache_file,
      'width' => $width,
      'height' => $height,
      'trim' => fallback($trim, false),
      'quality' => settings::get('image_thumbnail_quality'),
      'interlaced' => settings::get('image_thumbnail_interlaced'),
    ]);
  }

  function image_delete_cache($file) {

    $path = functions::file_webpath($file);

    $cache_name = sha1($path);

    foreach (glob('storage://cache/'. substr($cache_name, 0, 2) .'/' . $cache_name .'*') as $file) {
      unlink($file);
    }
  }
