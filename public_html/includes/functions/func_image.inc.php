<?php
  
  function image_delete_cache($file) {
  
    $webpath = str_replace(FS_DIR_HTTP_ROOT, '', $file);
    
    $cachename = sha1($webpath);
    
    $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_CACHE . $cachename .'*');
    
    if ($files) foreach($files as $file) {
      unlink($file);
    }
  }
  
  function image_resample($source, $target, $width=0, $height=0, $clipping='FIT_ONLY_BIGGER', $quality=90) {
    
  // If file does not exist
    if (!is_file($source)) {
      $source = FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png';
    }
    
  // If destination is a directory
    if (is_dir($target)) {
      
      if (substr($target, -1) != '/') $target .= '/';
      
      $source_webpath = str_replace(str_replace('\\', '/', realpath(FS_DIR_HTTP_ROOT)), '', str_replace('\\', '/', realpath($source)));
      
      switch (strtoupper($clipping)) {
        case 'CROP':
          $filename = sha1($source_webpath) . $width .'x'. $height .'_c.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'CROP_ONLY_BIGGER':
          $filename = sha1($source_webpath) . $width .'x'. $height .'_cob.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'STRETCH':
          $filename = sha1($source_webpath) . $width .'x'. $height .'_s.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'FIT':
          $filename = sha1($source_webpath) . $width .'x'. $height .'_f.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'FIT_USE_WHITESPACING':
          $filename = sha1($source_webpath) . $width .'x'. $height .'_fws.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'FIT_ONLY_BIGGER':
          $filename = sha1($source_webpath) . $width .'x'. $height .'_fob.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'FIT_ONLY_BIGGER_USE_WHITESPACING':
          $filename = sha1($source_webpath) . $width .'x'. $height .'_fobws.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        default:
          trigger_error('Unknown resample method ('.$clipping.') for image', E_USER_ERROR);
      }
      
  // If destination is a file
    } else {
      $filename = basename($target);
    }
    
    if (is_file($target . $filename)) {
      return str_replace(str_replace('\\', '/', realpath(FS_DIR_HTTP_ROOT)), '', str_replace('\\', '/', realpath($target . $filename)));
    }
    
    if (!$image = new ctrl_image($source)) {
      trigger_error('Could not create image object for resampling', E_USER_WARNING);
      return;
    }
    
    if (!$type = $image->type()) {
      trigger_error('Could not detect image type', E_USER_WARNING);
      return;
    }
    
    switch($type) {
      case 'jpg':
        $target_extension = 'jpg';
        break;
      case 'gif':
      case 'png':
      case 'bmp':
        $target_extension = 'png';
        break;
      default:
        $target_extension = 'png';
        break;
    }
    
    if (!$image->resample($width, $height, strtoupper($clipping))) return;
    
    if (!$image->write($target . $filename, $target_extension, $quality)) return;
    
    return str_replace(FS_DIR_HTTP_ROOT, '', str_replace('\\', '/', realpath($target . $filename)));
  }
  
  function image_thumbnail($source, $width=0, $height=0, $clipping='FIT_ONLY_BIGGER', $quality=65) {
    return image_resample($source, FS_DIR_HTTP_ROOT . WS_DIR_CACHE, $width, $height, $clipping, $quality);
  }
  
?>