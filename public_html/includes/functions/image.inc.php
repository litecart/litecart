<?php

  function image_delete_cache($file) {
  
    $webpath = str_replace(FS_DIR_HTTP_ROOT, '', $file);
    
    $cachename = sha1($webpath);
    
    $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_CACHE . $cachename .'*');
    
    if ($files) foreach($files as $file) {
      unlink($file);
    }
  }

  function image_resample($source, $target, $target_width=0, $target_height=0, $method='FIT_ONLY_BIGGER', $watermark=false) {
    
  // If file does not exist
    if (!is_file($source)) {
      $source = FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png';
    }
    
  // If destination is a directory
    if (is_dir($target)) {
      
    // Add trailing slash if missing
      if (substr($target, -1) != '/') $target .= '/';
      
    // Set webpath path of source
      $source_webpath = str_replace(realpath(FS_DIR_HTTP_ROOT), '', realpath($source));
      
    // Set filename
      switch ($method) {
        case 'CROP':
          $filename = sha1($source_webpath) . (($watermark) ? '_wm' : '_') . $target_width .'x'. $target_height .'_c.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'CROP_ONLY_BIGGER':
          $filename = sha1($source_webpath) . (($watermark) ? '_wm' : '_') . $target_width .'x'. $target_height .'_cob.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'STRETCH':
          $filename = sha1($source_webpath) . (($watermark) ? '_wm' : '_') . $target_width .'x'. $target_height .'_s.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'FIT':
          $filename = sha1($source_webpath) . (($watermark) ? '_wm' : '_') . $target_width .'x'. $target_height .'_f.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'FIT_USE_WHITESPACING':
          $filename = sha1($source_webpath) . (($watermark) ? '_wm' : '_') . $target_width .'x'. $target_height .'_fws.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'FIT_ONLY_BIGGER':
          $filename = sha1($source_webpath) . (($watermark) ? '_wm' : '_') . $target_width .'x'. $target_height .'_fob.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        case 'FIT_ONLY_BIGGER_USE_WHITESPACING':
          $filename = sha1($source_webpath) . (($watermark) ? '_wm' : '_') . $target_width .'x'. $target_height .'_fobws.'. pathinfo($source, PATHINFO_EXTENSION);
          break;
        default:
          trigger_error('Unknown resample method ('.$method.') for image', E_USER_ERROR);
      }
      
  // If destination is a file
    } else {
      
      $filename = basename($target);
    }
    
    if (is_file($target . $filename)) {
      return str_replace(realpath(FS_DIR_HTTP_ROOT), '', realpath($target . $filename));
    }
    
  // Create image object
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'image.inc.php');
    if (!$image = new ctrl_image($source)) return;
  
  // Set extension
	if (!$type = $image->type()) return;
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
    
    if (!$image->resample($target_width, $target_height, strtoupper($method))) return;
    
    //$image->filter('contrast');
    //$image->filter('sharpen');
    
    if ($watermark) {
      if (!$image->watermark($watermark, 'RIGHT', 'BOTTOM')) return;
    }
    
    if (!$image->write($target . $filename, $target_extension, 90)) return;
    
    return str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath($target . $filename));
  }
  
?>