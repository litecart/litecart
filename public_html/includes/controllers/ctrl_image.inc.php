<?php

//! The original imagecopyresampled function is broken. This is a fixed version of it. 
/*!
 *  \param dst_im Destination image
 *  \param src_im Source image
 *  \param dstX X coordinate of the top left corner of the destination area
 *  \param dstY Y coordinate of the top left corner of the destination area
 *  \param srcX X coordinate of the top left corner of the source area
 *  \param srcY Y coordinate of the top left corner of the source area
 *  \param dstW Width of the destination area
 *  \param dstH Height of the destination area
 *  \param srcW Width of the source area
 *  \param srcH Height of the source area
 */
  function ImageCopyResampledFixed(&$dst_im, &$src_im, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH) {
  // ImageCopyResampled does not take srcX and srcY into considaration, this is a bug. This fixes this. 
    $iSrcWidth = ImageSX($src_im);
    $iSrcHeight = ImageSY($src_im);
    $imgCropped = ImageCreateTrueColor($iSrcWidth-$srcX, $iSrcHeight-$srcY);
    ImageAlphaBlending($imgCropped, true);
    //ImageSaveAlpha($imgCropped, true);
    ImageFill($imgCropped, 0, 0, ImageColorAllocateAlpha($imgCropped, 255, 255, 255, 127));
    ImageCopy($imgCropped, $src_im, 0, 0, $srcX, $srcY, $iSrcWidth-$srcX, $iSrcHeight-$srcY);
    ImageCopyResampled($dst_im, $imgCropped, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
    ImageDestroy($imgCropped);
  }
  
########################################################################
  
  class ctrl_image {
    public $gdo;
    private $_src;
    private $_type;
    public $whitespace_color = '255,255,255';
    
    function __construct($file='') {
      if (!empty($file)) $this->_src = $file;
    }
    
    function load($file='') {
      
      $this->_src = empty($file) ? $this->_src : $file;
      
    // If file is an url
      if (substr($this->_src, 0, 7) == 'http://' || substr($this->_src, 0, 8) == 'https://') {
        return $this->load_from_string(functions::http_fetch($this->_src), pathinfo($this->_src, PATHINFO_EXTENSION));
      }
      
    // Make sure source is an existing file
      if (!is_file($this->_src)) {
        trigger_error('Source is not a valid image file ('. $this->_src.')', E_USER_WARNING);
        return false;
      }
      
    // Make sure the file is readable
      if (!is_readable($this->_src)) {
        trigger_error('Source is not a readable image ('. $this->_src .')', E_USER_WARNING);
        return false;
      }
      
    // Create image object
      switch(exif_imagetype($this->_src)) {
        case 1:
          $this->_type = 'gif';
          $this->gdo = ImageCreateFromGIF($this->_src);
          break;
        case 2:
          $this->_type = 'jpg';
          $this->gdo = ImageCreateFromJPEG($this->_src);
          break;
        case 3:
          $this->_type = 'png';
          $this->gdo = ImageCreateFromPNG($this->_src);
          break;
        case 15:
          $this->_type = 'bmp';
          $this->gdo = ImageCreateFromWBMP($this->_src);
          break;
        case 16:
          $this->_type = 'xbm';
          $this->gdo = ImageCreateFromXBM($this->_src);
          break;
        default:
          trigger_error('Unknown image type', E_USER_WARNING);
      }
      
      if (!is_resource($this->gdo)) {
        trigger_error('Could not load image ('. $this->_src .').', E_USER_WARNING);
        return false;
      }
	  
	  return true;
    }
    
    function load_from_string($string, $type='jpg') {
      
      $this->gdo = ImageCreateFromString($string);
      $this->_type = 'jpg';
      
      if (!is_resource($this->gdo)) {
        trigger_error('Could not load image from string.', E_USER_WARNING);
        return false;
      }
      
      return true;
    }
    
    function resample($width=1024, $height=1024, $method='FIT_ONLY_BIGGER') {
      
    // Load source image if object is missing
      if (!$this->gdo) {
        $this->load();
      }
      
    // Halt on no image object
      if (!$this->gdo) {
        return false;
      }
      
    // Return if missing dimensions
      if ($this->width() == 0 || $this->height() == 0) {
        trigger_error('Error getting source image dimensions ('. $this->_src .').', E_USER_WARNING);
        return false;
      }
      
    // Calculate source's dimensional ratio
      $source_ratio = $this->width() / $this->height();
      
    // Convert percentage dimensions to pixels
      if (strpos($width, '%')) $width = $this->width() * str_replace('%', '', $width) / 100;
      if (strpos($height, '%')) $height = $this->height() * str_replace('%', '', $height) / 100;
      
    // Complete missing single sides
      if ($width == 0) {
        $width = round($height * $source_ratio);
      }
      if ($height == 0) {
        $height = round($width / $source_ratio);
      }
      
    // Calculate new size
      switch (strtoupper($method)) {
      
        case 'CROP':
        case 'CROP_ONLY_BIGGER':
        
        // Calculate dimensions
          $destination_width = $width;
          $destination_height = $height;
          
          if ($method == 'CROP_ONLY_BIGGER') {
            if ($this->width() < $destination_width) {
              $destination_width = $this->width();
            }
            if ($this->height() < $destination_height) {
              $destination_height = $this->height();
            }
          }
          
        // Create output image container
          $oResized = ImageCreateTrueColor($destination_width, $destination_height);
          
        // Calculate destination dimensional ratio
          $destination_ratio = $destination_width / $destination_height;
          
          ImageAlphaBlending($oResized, false);
          ImageSaveAlpha($oResized, true);
          
          ImageFill($oResized, 0, 0, ImageColorAllocateAlpha($oResized, 255, 255, 255, 127));
          
        // Perform resample
          if (($this->width() / $destination_width) > ($this->height() / $destination_height)) {
            ImageCopyResampledFixed($oResized, $this->gdo, 0, 0, ($this->width() - $destination_width * $this->height() / $destination_height) / 2, 0, $destination_width, $destination_height, $this->height() * $destination_ratio, $this->height());
          } else {
            ImageCopyResampledFixed($oResized, $this->gdo, 0, 0, 0, ($this->height() - $destination_height * $this->width() / $destination_width) / 2, $destination_width, $destination_height, $this->width(), $this->width() / $destination_ratio);
          }
          
          break;
        
        case 'STRETCH':
        
        // Calculate dimensions
          $destination_width = ($width == 0) ? $this->width() : $width;
          $destination_height = ($height == 0) ? $this->height() : $height;
          
        // Create output image container
          $oResized = ImageCreateTrueColor($destination_width, $destination_height);
          
          ImageAlphaBlending($oResized, false);
          ImageSaveAlpha($oResized, true);
          
          ImageFill($oResized, 0, 0, ImageColorAllocateAlpha($oResized, 255, 255, 255, 127));
          
        // Perform resample
          ImageCopyResampledFixed($oResized, $this->gdo, ($width - $destination_width) / 2, ($height - $destination_height) / 2, 0, 0, $destination_width, $destination_height, $this->width(), $this->height());         
          
          break;
          
        case 'FIT':
        case 'FIT_USE_WHITESPACING':
        case 'FIT_ONLY_BIGGER':
        case 'FIT_ONLY_BIGGER_USE_WHITESPACING':
        
        // Calculate dimensions
          $destination_width = $width;
          $destination_height = round($destination_width / $source_ratio);
          if ($destination_height > $height) {
            $destination_height = $height;
            $destination_width = round($destination_height * $source_ratio);
          }
          
          if ($method == 'FIT_ONLY_BIGGER' || $method == 'FIT_ONLY_BIGGER_USE_WHITESPACING') {
            if ($destination_width > $destination_height) {
              if ($destination_width > $this->width()) {
                $destination_width = $this->width();
                $destination_height = round($destination_width / $source_ratio);
              }
            } else {
              if ($destination_height > $this->height()) {
                $destination_height = $this->height();
                $destination_width = round($destination_height * $source_ratio);
              }
            }
          }
          
          if ($method == 'FIT_USE_WHITESPACING' || $method == 'FIT_ONLY_BIGGER_USE_WHITESPACING') {
          
          // Create output image container
            $oResized = ImageCreateTrueColor($width, $height);
            
            ImageAlphaBlending($oResized, false);
            ImageSaveAlpha($oResized, true);
            
          // Fill with whitespace color
            ImageFill($oResized, 0, 0, ImageColorAllocateAlpha($oResized, 255, 255, 255, 127));
            
          // Make whitespace color transparent
            //ImageColorTransparent($oResized, ImageColorAllocate($oResized, 255, 255, 255));
            
          // Perform resample
            ImageCopyResampled($oResized, $this->gdo, ($width - $destination_width) / 2, ($height - $destination_height) / 2, 0, 0, $destination_width, $destination_height, $this->width(), $this->height());         
            
          } else {
          
          // Create output image container
            $oResized = ImageCreateTrueColor($destination_width, $destination_height);
            
            ImageAlphaBlending($oResized, false);
            ImageSaveAlpha($oResized, true);
            
            ImageFill($oResized, 0, 0, ImageColorAllocateAlpha($oResized, 255, 255, 255, 127));
            
          // Perform resample
            ImageCopyResampledFixed($oResized, $this->gdo, ($width - $destination_width) / 2, ($height - $destination_height) / 2, 0, 0, $destination_width, $destination_height, $this->width(), $this->height());         
          }
          
          break;
          
        default:
          return false;
      }
      
    // Set new dimensions for object
      $this->width = $destination_width;
      $this->height = $destination_height;
      
    // Destroy old object
      ImageDestroy($this->gdo);
      
    // Set resized object as main object
      $this->gdo = $oResized;
      
      return true;
    }
    
    function filter($filter) {
    
    // Load image object if not made previously
      if (!is_resource($this->gdo)) $this->load();
      
    // Perform filter effect
      switch($filter) {
        case 'contrast':
          imagefilter($this->gdo, IMG_FILTER_CONTRAST, -2);
          break;
        case 'gamma':
          imagegammacorrect($this->gdo, 1.0, 1.25);
        case 'gaussian_blur':
          imagefilter($this->gdo, IMG_FILTER_GAUSSIAN_BLUR);
          break;
        case 'pixelate':
          $imagex = imagesx($this->gdo);
          $imagey = imagesy($this->gdo);
          $blocksize = 12;
          for ($x = 0; $x < $imagex; $x += $blocksize) {
            for ($y = 0; $y < $imagey; $y += $blocksize) {
              $rgb = imagecolorat($this->gdo, $x, $y);
              imagefilledrectangle($this->gdo, $x, $y, $x + $blocksize - 1, $y + $blocksize - 1, $rgb);
            }
          }
          break;
        case 'sepia':
          imagefilter($this->gdo, IMG_FILTER_GRAYSCALE);
          imagefilter($this->gdo, IMG_FILTER_COLORIZE, 100, 50, 0);
          break;
        case 'sharpen':
          $matrix = array(array(-1,-1,-1), array(-1,16,-1), array(-1,-1,-1));
          $divisor = array_sum(array_map('array_sum', $matrix));
          $offset = 0;
          ImageConvolution($this->gdo, $matrix, $divisor, $offset);
          break;
        case 'selective_blur':
          imagefilter($this->gdo, IMG_FILTER_SELECTIVE_BLUR);
          break;
        default:
          trigger_error('Unknown filter effect for image');
      }
	  
	  return true;
    }
    
    function watermark($watermark, $align_x, $align_y, $margin=5) {
    
    // Load image object if not made previously
      if (!is_resource($this->gdo)) $this->load();
      
    // Create image object
      $oWatermark = new ctrl_image($watermark);
      
    // Return false on no image
      if (!$oWatermark->type()) {
        trigger_error('Watermark file is not a valid image: '. $watermark, E_USER_WARNING);
        return false;
      }
      
    // Load watermark
      $oWatermark->load();
      
    // Check if watermark is a PNG file
      if ($oWatermark->type() != 'png') {
        trigger_error('Watermark file is not a PNG image: '. $watermark, E_USER_NOTICE);
      }
      
    // Initialize alpha channel for PNG transparency
      ImageAlphaBlending($this->gdo, true);
      
    // Align watermark and set horizontal offset
      switch (strtoupper($align_x)) {
        case "LEFT":
          $offset_x = $margin;
          break;
        case "CENTER":
          $offset_x = round(($this->width() - $oWatermark->width()) / 2);
          break;
        case "RIGHT":
        default:
          $offset_x = $this->width() - $oWatermark->width() - $margin;
          break;
      }
      
      // Align watermark and set vertical offset
      switch (strtoupper($align_y)) {
        case "TOP":
          $offset_y = $margin;
          break;
        case "MIDDLE":
          $offset_y = round(($this->height() - $oWatermark->height()) / 2);
          break;
        case "BOTTOM":
        default:
          $offset_y = $this->height() - $oWatermark->height() - $margin;
          break;
      }
      
      // Create the watermarked image
        ImageCopy($this->gdo, $oWatermark->gdo, $offset_x, $offset_y, 0, 0, $oWatermark->width(), $oWatermark->height());
      
      // Free some RAM memory
        ImageDestroy($oWatermark->gdo);
        
        return true;
    }
    
  // Dump image data to disk (Returns true if successful)
    function write($destination, $type='jpg', $quality=90) {

    // Return false if target already exists
      if (is_file($destination)) {
        trigger_error('Destination already exists: '. $destination, E_USER_WARNING);
        return false;
      }
      
    // Return false if target is folder
      if (is_dir($destination)) {
        trigger_error('Destination is a folder: '. $destination, E_USER_WARNING);
        return false;
      }
      
    if (!is_writable(pathinfo($destination, PATHINFO_DIRNAME))) {
      trigger_error('Destination is not writable: '. $destination .'.', E_USER_WARNING);
      return false;
    }
      
    // Load image object if not made previously
      if (!is_resource($this->gdo)) $this->load();

    // If not set to force type, get type from target filename.
      if (!$type) {
        $extension = pathinfo($destination, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), array('gif', 'jpg', 'png'))) {
          $type = strtolower($extension);
        } else {
          trigger_error('Unknown output format.', E_USER_WARNING);
          return false;
        }
      }
      
    // Write the image to disk
      switch(strtolower($type)) {
        case "gif":
          $oNonAlpha = ImageCreateTrueColor(imagesx($this->gdo), imagesy($this->gdo));
          ImageAlphaBlending($oNonAlpha, true);
          ImageFill($oNonAlpha, 0, 0, ImageColorAllocate($oNonAlpha, 255, 255, 255, 0));
          ImageCopy($oNonAlpha, $this->gdo, 0, 0, 0, 0, imagesx($this->gdo), imagesy($this->gdo));
          ImageAlphaBlending($oNonAlpha, false);
          imagetruecolortopalette($oNonAlpha, false, 255);
          ImageGIF($oNonAlpha, $destination);
          ImageDestroy($this->gdo);
          ImageDestroy($oNonAlpha);
          return true;
        case "jpg":
          $oNonAlpha = ImageCreateTrueColor(imagesx($this->gdo), imagesy($this->gdo));
          ImageAlphaBlending($oNonAlpha, true);
          ImageFill($oNonAlpha, 0, 0, ImageColorAllocateAlpha($oNonAlpha, 255, 255, 255, 0));
          ImageCopy($oNonAlpha, $this->gdo, 0, 0, 0, 0, imagesx($this->gdo), imagesy($this->gdo));
          ImageAlphaBlending($oNonAlpha, false);
          ImageJPEG($oNonAlpha, $destination, $quality);
          ImageDestroy($this->gdo);
          ImageDestroy($oNonAlpha);
          return true;
        case "png":
          ImageSaveAlpha($this->gdo, true);
          ImagePNG($this->gdo, $destination);
          ImageDestroy($this->gdo);
          return true;
        default:
          ImageDestroy($this->gdo);
          return false;
      }
      
      return true;
    }
    
  // Dump image data to disk (Returns true if successful)
    function output($type='jpg', $quality=90) {
    
    // Load image object if not made previously
      if (!is_resource($this->gdo)) $this->load();
      
    // Write the image to disk
      switch($type) {
        case "gif":
          header('Content-type: image/gif');
          ImageGIF($this->gdo, false);
          break;
        case "jpg":
          header('Content-type: image/jpeg');
          ImageJPEG($this->gdo, false, $quality);
          break;
        case "png":
          header('Content-type: image/png');
          ImagePNG($this->gdo, false);
          break;
        default:
          trigger_error('Uknown output format');
          return false;
      }
      
      return true;
    }
    
    function destroy() {
    
    // Load image object if not made previously
      if (is_resource($this->gdo)) {
        ImageDestroy($this->gdo);
	  }
      return true;
    }
    
    function width() {
    
    // Load image object if not made previously
      if (!is_resource($this->gdo)) $this->load();
      
      return ImageSX($this->gdo);
    }
    
    function height() {
    
    // Load image object if not made previously
      if (!is_resource($this->gdo)) $this->load();
      
      return ImageSY($this->gdo);
    }
    
    function type() {
    
    // Load image object if not made previously
      if (!is_resource($this->gdo)) $this->load();
      
      return $this->_type;
    }
  }

?>