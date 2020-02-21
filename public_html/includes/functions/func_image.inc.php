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

      if (!is_file($source)) $source = FS_DIR_APP . 'images/no_image.png';

      $options = [
        'destination' => !empty($options['destination']) ? $options['destination'] : FS_DIR_APP . 'cache/',
        'width' => !empty($options['width']) ? $options['width'] : 0,
        'height' => !empty($options['height']) ? $options['height'] : 0,
        'clipping' => !empty($options['clipping']) ? $options['clipping'] : 'FIT_ONLY_BIGGER',
        'quality' => isset($options['quality']) ? $options['quality'] : settings::get('image_quality'),
        'trim' => !empty($options['trim']) ? $options['trim'] : false,
        'interlaced' => !empty($options['interlaced']) ? true : false,
        'overwrite' => !empty($options['overwrite']) ? $options['overwrite'] : false,
        'watermark' => !empty($options['watermark']) ? $options['watermark'] : false,
      ];

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
            trigger_error("Unknown image clipping method ($clipping)", E_USER_WARNING);
            return;
        }

        $path = preg_replace('#^('. preg_quote(FS_DIR_APP, '#') .')#', '', str_replace('\\', '/', realpath($source)));

        $options['destination'] .= implode('', [
          sha1($path),
          !empty($options['trim']) ? '_t' : null,
          '_'.(int)$options['width'] .'x'. (int)$options['height'],
          $clipping_filename_flag,
          !empty($options['watermark']) ? '_wm' : null,
          !empty($options['interlaced']) ? '_i' : null,
          '.' . pathinfo($source, PATHINFO_EXTENSION),
        ]);
      }

    // Return an already existing file
      if (is_file($options['destination'])) {
        if (!empty($options['overwrite'])) {
          unlink($options['destination']);
        } else {
          return preg_replace('#^('. preg_quote(FS_DIR_APP, '#') .')#', '', str_replace('\\', '/', realpath($options['destination'])));
        }
      }

      if (!$image = new ent_image($source)) return;

      if (!empty($options['trim'])) {
        $image->trim();
      }

      if ($options['width'] != 0 || $options['height'] != 0) {
        if (!$image->resample($options['width'], $options['height'], strtoupper($options['clipping']))) return;
      }

      if (!empty($options['watermark'])) {
        if ($options['watermark'] === true) $options['watermark'] = FS_DIR_APP . 'images/logotype.png';
        if (!$image->watermark($options['watermark'], 'RIGHT', 'BOTTOM')) return;
      }

      if (!is_dir(FS_DIR_APP . 'cache/' . substr(basename($options['destination']), 0, 2))) {
        if (!mkdir(FS_DIR_APP . 'cache/' . substr(basename($options['destination']), 0, 2), 0777)) {
          trigger_error('Could not create cache subfolder', E_USER_WARNING);
          return false;
        }
      }

      if (!$image->write($options['destination'], $options['quality'], !empty($options['interlaced']))) return;

      return preg_replace('#^('. preg_quote(FS_DIR_APP, '#') .')#', '', str_replace('\\', '/', realpath($options['destination'])));

    } catch (Exception $e) {
      trigger_error($e->getMessage() , E_USER_WARNING);
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

    if (isset($_SERVER['HTTP_ACCEPT']) && preg_match('#image/webp#', $_SERVER['HTTP_ACCEPT'])) {
      $extension = 'webp';
    } else {
      $extension = pathinfo($source, PATHINFO_EXTENSION);
    }

    $path = preg_replace('#^('. preg_quote(FS_DIR_APP, '#') .')#', '', str_replace('\\', '/', realpath($source)));

    if (pathinfo($source, PATHINFO_EXTENSION) == 'svg') {
      return $path;
    }

    $filename = implode('', [
      sha1($path),
      $trim ? '_t' : null,
      '_'.(int)$width .'x'. (int)$height,
      $clipping_filename_flag,
      settings::get('image_thumbnail_interlaced') ? '_i' : null,
      '.webp',
    ]);

    if (!is_dir(FS_DIR_APP . 'cache/' . substr($filename, 0, 2))) {
      if (!mkdir(FS_DIR_APP . 'cache/' . substr($filename, 0, 2))) {
        trigger_error('Could not create cache subfolder', E_USER_WARNING);
        return false;
      }
    }

    return image_process($source, [
      'destination' => FS_DIR_APP . 'cache/' . substr($filename, 0, 2) . '/' . $filename,
      'width' => $width,
      'height' => $height,
      'clipping' => $clipping,
      'trim' => !empty($trim) ? $trim : false,
      'quality' => settings::get('image_thumbnail_quality'),
      'interlaced' => settings::get('image_thumbnail_interlaced'),
    ]);
  }

  function image_delete_cache($file) {

    $webpath = str_replace(DOCUMENT_ROOT, '', $file);

    $cachename = sha1($webpath);

    $files = glob(FS_DIR_APP . 'cache/'. substr($cachename, 0, 3) .'/' . $cachename .'*');

    if ($files) foreach ($files as $file) {
      unlink($file);
    }
  }
