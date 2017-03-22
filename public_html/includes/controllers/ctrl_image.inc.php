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
  if (!function_exists('ImageCopyResampledFixed')) {
    function ImageCopyResampledFixed(&$dst_im, &$src_im, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH, $whiteSpace) {
      $iSrcWidth = ImageSX($src_im);
      $iSrcHeight = ImageSY($src_im);
      $imgCropped = ImageCreateTrueColor($iSrcWidth-$srcX, $iSrcHeight-$srcY);
      ImageAlphaBlending($imgCropped, true);
      ImageFill($imgCropped, 0, 0, ImageColorAllocateAlpha($imgCropped, $whiteSpace[0], $whiteSpace[1], $whiteSpace[2], 127));
      ImageCopy($imgCropped, $src_im, 0, 0, $srcX, $srcY, $iSrcWidth-$srcX, $iSrcHeight-$srcY);
      $result = ImageCopyResampled($dst_im, $imgCropped, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
      ImageDestroy($imgCropped);
      return $result;
    }
  }

########################################################################

  class ctrl_image {
    private $_library;
    private $_image;
    private $_src;
    private $_type;
    private $_width;
    private $_height;
    private $_whitespace;

    public function __construct($file=null, $force_library=null) {

    // Set library
      if (!empty($force_library)) {
        $this->_library = $force_library;
      } else if (extension_loaded('imagick')) {
        $this->_library = 'imagick';
      } else if (extension_loaded('gd')) {
        $this->_library = 'gd';
      } else {
        trigger_error('No image processing library available', E_USER_ERROR);
      }

      if (!empty($file)) $this->set($file);

      $this->_whitespace = explode(',', settings::get('image_whitespace_color'));
    }

    public function set($file) {
      $this->_src = $file;
      $this->_image = null;
      $this->_width = null;
      $this->_height = null;
      $this->_type = null;
    }

    public function load($file=null) {

      if (!empty($file)) $this->_src = $file;

      if (empty($this->_src)) {
        trigger_error('No source image file set', E_USER_WARNING);
        return false;
      }

      switch($this->_library) {
        case 'imagick':
          try {
            $this->_image = new imagick($this->_src);
            return true;
          } catch (Exception $e) {
            trigger_error($e->getMessage() .' ('.$this->_src.')', E_USER_WARNING);
            return false;
          }

        case 'gd':
          switch(strtolower(pathinfo($this->_src, PATHINFO_EXTENSION))) {
            case 'gif':
              $this->_type = 'gif';
              $this->_image = ImageCreateFromGIF($this->_src);
              break;

            case 'jpg':
            case 'jpeg':
              $this->_type = 'jpg';
              $this->_image = ImageCreateFromJPEG($this->_src);
              break;

            case 'png':
              $this->_type = 'png';
              $this->_image = ImageCreateFromPNG($this->_src);
              break;

            default:
              $this->load_from_string(file_get_contents($this->_src));
              break;
          }

          return is_resource($this->_image) ? true : false;
      }
    }

    public function load_from_string($binary, $type='png') {

      $this->_type = $type;
      $this->_width = null;
      $this->_height = null;

      switch($this->_library) {

        case 'imagick':

          try {
            return $this->_image->readImageBlob($binary);
          } catch (Exception $e) {
            trigger_error($e->getMessage() . ' ('.$this->_src.')', E_USER_WARNING);
            return false;
          }

        case 'gd':

          $this->_type = $type;
          $this->_image = ImageCreateFromString($binary);

          if (!is_resource($this->_image)) return false;

          return true;
      }
    }

    public function resample($width=1024, $height=1024, $clipping='FIT_ONLY_BIGGER') {

      if ($width == 0 && $height == 0) return;

      if ($this->width() == 0 || $this->height() == 0) {
        trigger_error('Error getting source image dimensions ('. $this->_src .').', E_USER_WARNING);
        return false;
      }

    // Convert percentage dimensions to pixels
      if (strpos($width, '%')) $width = $this->width() * str_replace('%', '', $width) / 100;
      if (strpos($height, '%')) $height = $this->height() * str_replace('%', '', $height) / 100;

    // Calculate source proportion
      $source_ratio = $this->width() / $this->height();

    // Complete missing target dimensions
      if ($width == 0) $width = round($height * $source_ratio);
      if ($height == 0) $height = round($width / $source_ratio);

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            trigger_error('Not a valid image object', E_USER_WARNING);
            return false;
          }

          try {
            $this->_image->setImageBackgroundColor('rgba('.$this->_whitespace[0].','.$this->_whitespace[1].','.$this->_whitespace[2].',0)');

            switch(strtoupper($clipping)) {
              case 'FIT':
                //$result = $this->_image->scaleImage($width, $height, true);
                //return $this->_image->adaptiveResizeImage($width, $height, true);
                return $this->_image->thumbnailImage($width, $height, true);

              case 'FIT_ONLY_BIGGER':
                if ($this->width() <= $width && $this->height() <= $height) return true;

                return $this->_image->thumbnailImage($width, $height, true);

              case 'FIT_USE_WHITESPACING':
                return $this->_image->thumbnailImage($width, $height, true, true);

              case 'FIT_ONLY_BIGGER_USE_WHITESPACING':
                if ($this->width() <= $width && $this->height() <= $height) {
                  $_newimage = new imagick();
                  $_newimage->newImage($width, $height, 'rgba('.$this->_whitespace[0].','.$this->_whitespace[1].','.$this->_whitespace[2].',0)');
                  $offset_x = round(($width - $this->width()) / 2);
                  $offset_y = round(($height - $this->height()) / 2);
                  $result = $_newimage->compositeImage($this->_image, imagick::COMPOSITE_OVER, $offset_x, $offset_y);
                  $this->_image = $_newimage;
                  return $result;
                }

                return $this->_image->thumbnailImage($width, $height, true, true);

              case 'CROP':

                return $this->_image->cropThumbnailImage($width, $height);

              case 'CROP_ONLY_BIGGER':
                if ($this->width() <= $width && $this->height() <= $height) return true;
                return $this->_image->cropThumbnailImage($width, $height);

              case 'STRETCH':
                //return $this->_image->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1); // Stretch
                //return $this->_image->adaptiveResizeImage($width, $height, false); // Stretch
                return $this->_image->thumbnailImage($width, $height, false); // Stretch

            default:
              trigger_error('Unknown clipping method ($clipping)', E_USER_WARNING);
              return false;
            }
          } catch (Exception $e) {
            trigger_error($e->getMessage() .' {$width}x{$height} ($this->_src)', E_USER_WARNING);
            return false;
          }

        case 'gd':

          if (!is_resource($this->_image)) $this->load();

          if (!is_resource($this->_image)) {
            trigger_error('Not a valid image resource', E_USER_WARNING);
            return false;
          }

        // Calculate new size
          switch (strtoupper($clipping)) {

            case 'CROP':
            case 'CROP_ONLY_BIGGER':

            // Calculate dimensions
              $destination_width = $width;
              $destination_height = $height;

              if (strtoupper($clipping) == 'CROP_ONLY_BIGGER') {
                if ($this->width() < $destination_width) {
                  $destination_width = $this->width();
                }
                if ($this->height() < $destination_height) {
                  $destination_height = $this->height();
                }
              }

            // Create output image container
              $_resized = ImageCreateTrueColor($destination_width, $destination_height);

            // Calculate destination dimensional ratio
              $destination_ratio = $destination_width / $destination_height;

              ImageAlphaBlending($_resized, true);
              ImageSaveAlpha($_resized, true);

              ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[0], $this->_whitespace[0], 127));

            // Perform resample
              if (($this->width() / $destination_width) > ($this->height() / $destination_height)) {
                ImageCopyResampledFixed($_resized, $this->_image, 0, 0, ($this->width() - $destination_width * $this->height() / $destination_height) / 2, 0, $destination_width, $destination_height, $this->height() * $destination_ratio, $this->height(), $this->_whitespace);
              } else {
                ImageCopyResampledFixed($_resized, $this->_image, 0, 0, 0, ($this->height() - $destination_height * $this->width() / $destination_width) / 2, $destination_width, $destination_height, $this->width(), $this->width() / $destination_ratio, $this->_whitespace);
              }

              break;

            case 'STRETCH':

            // Calculate dimensions
              $destination_width = ($width == 0) ? $this->width() : $width;
              $destination_height = ($height == 0) ? $this->height() : $height;

            // Create output image container
              $_resized = ImageCreateTrueColor($destination_width, $destination_height);

              ImageAlphaBlending($_resized, true);
              ImageSaveAlpha($_resized, true);

              ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

            // Perform resample
              ImageCopyResampledFixed($_resized, $this->_image, ($width - $destination_width) / 2, ($height - $destination_height) / 2, 0, 0, $destination_width, $destination_height, $this->width(), $this->height(), $this->_whitespace);

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

              if (in_array(strtoupper($clipping), array('FIT_ONLY_BIGGER', 'FIT_ONLY_BIGGER_USE_WHITESPACING'))) {
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

              if (in_array(strtoupper($clipping), array('FIT_USE_WHITESPACING', 'FIT_ONLY_BIGGER_USE_WHITESPACING'))) {

              // Create output image container
                $_resized = ImageCreateTrueColor($width, $height);

                ImageAlphaBlending($_resized, true);
                ImageSaveAlpha($_resized, true);

              // Fill with whitespace color
                ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

              // Make whitespace color transparent
                //ImageColorTransparent($_resized, ImageColorAllocate($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2]));

              // Perform resample
                ImageCopyResampled($_resized, $this->_image, ($width - $destination_width) / 2, ($height - $destination_height) / 2, 0, 0, $destination_width, $destination_height, $this->width(), $this->height());

              } else {

              // Create output image container
                $_resized = ImageCreateTrueColor($destination_width, $destination_height);

                ImageAlphaBlending($_resized, true);
                ImageSaveAlpha($_resized, true);

                ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

              // Perform resample
                ImageCopyResampledFixed($_resized, $this->_image, ($width - $destination_width) / 2, ($height - $destination_height) / 2, 0, 0, $destination_width, $destination_height, $this->width(), $this->height(), $this->_whitespace);
              }

              break;

            default:
              trigger_error('Unknown clipping method', E_USER_WARNING);
              return false;
          }

          $this->_width = $destination_width;
          $this->_height = $destination_height;

          ImageDestroy($this->_image);
          $this->_image = $_resized;

          return true;
      }
    }

    public function filter($filter) {

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            trigger_error('Not a valid image object', E_USER_WARNING);
            return false;
          }

          switch($filter) {
            case 'blur':
              //return $this->_image->gaussianBlurImage(2, 3);
              return $this->_image->blurImage(2, 3);

            case 'contrast':
              return $this->_image->contrastImage(2);

            case 'gamma':
              return $this->gammaImage(1.25);

            case 'pixelate':
              $width = $this->_image->getImageWidth();
              $this->_image->scaleImage($width/10, 0);
              return $this->_image->scaleImage($width, 0);

            case 'sepia':
              return $this->_image->sepiaToneImage(80);

            case 'sharpen':
              return $this->_image->sharpenImage(2, 3);

            default:
              trigger_error('Unknown filter effect for image', E_USER_WARNING);
              return false;
          }

        case 'gd':

          if (!is_resource($this->_image)) $this->load();

          if (!is_resource($this->_image)) {
            trigger_error('Not a valid image resource', E_USER_WARNING);
            return false;
          }

          switch($filter) {
            case 'contrast':
              imagefilter($this->_image, IMG_FILTER_CONTRAST, -2);
              return true;

            case 'gamma':
              imagegammacorrect($this->_image, 1.0, 1.25);
              return true;

            case 'blur':
              imagefilter($this->_image, IMG_FILTER_GAUSSIAN_BLUR);
              //imagefilter($this->_image, IMG_FILTER_SELECTIVE_BLUR);
              return true;

            case 'pixelate':
              $imagex = imagesx($this->_image);
              $imagey = imagesy($this->_image);
              $blocksize = 12;
              for ($x = 0; $x < $imagex; $x += $blocksize) {
                for ($y = 0; $y < $imagey; $y += $blocksize) {
                  $rgb = imagecolorat($this->_image, $x, $y);
                  imagefilledrectangle($this->_image, $x, $y, $x + $blocksize - 1, $y + $blocksize - 1, $rgb);
                }
              }
              return true;

            case 'sepia':
              imagefilter($this->_image, IMG_FILTER_GRAYSCALE);
              imagefilter($this->_image, IMG_FILTER_COLORIZE, 100, 50, 0);
              return true;

            case 'sharpen':
              $matrix = array(array(-1,-1,-1), array(-1,16,-1), array(-1,-1,-1));
              $divisor = array_sum(array_map('array_sum', $matrix));
              $offset = 0;
              ImageConvolution($this->_image, $matrix, $divisor, $offset);
              return true;

            default:
              trigger_error('Unknown filter effect for image', E_USER_WARNING);
              return false;
          }
      }
    }

    public function trim() {

      $this->_width = null;
      $this->_height = null;

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            trigger_error('Not a valid image object', E_USER_WARNING);
            return false;
          }

          try {
            return $this->_image->trimImage(0);
          } catch (Exception $e) {
            trigger_error($e->getMessage() . ' ('.$this->_src.')', E_USER_WARNING);
          }

        case 'gd':

          if (!is_resource($this->_image)) $this->load();

          if (!is_resource($this->_image)) {
            trigger_error('Not a valid image resource', E_USER_WARNING);
            return false;
          }

          //if (function_exists('imagecropauto')) { // PHP 5.5
          //  return ImageCropAuto($this->_image, IMG_CROP_SIDES); // Doesn't do it's job properly
          //  return ImageCropAuto($this->_image, IMG_CROP_THRESHOLD, 100, imagecolorat($this->_image, 0, 0)); // Doesn't do it's job properly
          //}

          $hexcolor = imagecolorat($this->_image, 0,0);
          $top = $left = 0;
          $right = $original_x = $width = $this->width();
          $bottom = $original_y = $height = $this->height();

          $this->_width = null;
          $this->_height = null;

          do {
          // Top
            for (; $top < $original_y; ++$top) {
              for ($x = 0; $x < $original_x; ++$x) {
                if (@imagecolorat($this->_image, $x, $top) != $hexcolor) {
                  break 2;
                }
              }
            }

          // Stop if all pixels are trimmed
            if ($top == $bottom) {
              $top = 0;
              $code = 2;
              break 1;
            }

            // Bottom
            for (; $bottom > 0; --$bottom) {
              for ($x = 0; $x < $original_x; ++$x) {
                if (@imagecolorat($this->_image, $x, $bottom-1) != $hexcolor) {
                  break 2;
                }
              }
            }

          // Left
            for (; $left < $original_x; ++$left) {
              for ($y = $top; $y <= $bottom; ++$y) {
                if (@imagecolorat($this->_image, $left, $y) != $hexcolor) {
                  break 2;
                }
              }
            }

          // Right
            for (; $right > 0; --$right) {
              for ($y = $top; $y <= $bottom; ++$y) {
                if (@imagecolorat($this->_image, $right-1, $y) != $hexcolor) {
                  break 2;
                }
              }
            }

            $width = $right - $left;
            $height = $bottom - $top;
            $code = ($width < $original_x || $height < $original_y) ? 1 : 0;
          } while (0);

          $padding = $width * 0.1; // Set padding size in px

          $_image = ImageCreateTrueColor($width + ($padding * 2), $height + ($padding * 2));
          ImageAlphaBlending($_image, true);
          ImageFill($_image, 0, 0, ImageColorAllocateAlpha($_image, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 0));

          $result = ImageCopy($_image, $this->_image, $padding, $padding, $left, $top, $width, $height);

          if ($result) {
            ImageDestroy($this->_image);
            $this->_image = $_image;
            $this->_width = $width + ($padding*2);
            $this->_height = $height + ($padding*2);
          }

          return $result;
      }
    }

    public function watermark($watermark, $align_x='RIGHT', $align_y='BOTTOM', $margin=5) {

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            trigger_error('Not a valid image object', E_USER_WARNING);
            return false;
          }

          $_watermark = new imagick();
          $_watermark->readImage($watermark);

          switch (strtoupper($align_x)) {
            case 'LEFT':
              $offset_x = $margin;
              break;
            case 'CENTER':
              $offset_x = round(($this->width() - $_watermark->getImageWidth()) / 2);
              break;
            case 'RIGHT':
            default:
              $offset_x = $this->width() - $_watermark->getImageWidth() - $margin;
              break;
          }

          switch (strtoupper($align_y)) {
            case 'TOP':
              $offset_y = $margin;
              break;
            case 'CENTER':
            case 'MIDDLE':
              $offset_y = round(($this->height() - $_watermark->getImageHeight()) / 2);
              break;
            case 'BOTTOM':
            default:
              $offset_y = $this->height() - $_watermark->getImageHeight() - $margin;
              break;
          }

          return $this->_image->compositeImage($_watermark, imagick::COMPOSITE_OVER, $offset_x, $offset_y);

        case 'gd':

          if (!is_resource($this->_image)) $this->load();

          if (!is_resource($this->_image)) {
            trigger_error('Not a valid image resource', E_USER_WARNING);
            return false;
          }

          $_watermark = new ctrl_image($watermark, $this->_library);

        // Return false on no image
          if (!$_watermark->type()) {
            trigger_error('Watermark file is not a valid image: '. $watermark, E_USER_WARNING);
            return false;
          }

        // Load watermark
          $_watermark->load();

        // Check if watermark is a PNG file
          if ($_watermark->type() != 'png') {
            trigger_error('Watermark file is not a PNG image: '. $watermark, E_USER_NOTICE);
          }

        // Shrink a large watermark
          $_watermark->resample($this->width()/3, $this->height()/3, 'FIT_ONLY_BIGGER');

        // Align watermark and set horizontal offset
          switch (strtoupper($align_x)) {
            case 'LEFT':
              $offset_x = $margin;
              break;
            case 'CENTER':
              $offset_x = round(($this->width() - $_watermark->width()) / 2);
              break;
            case 'RIGHT':
            default:
              $offset_x = $this->width() - $_watermark->width() - $margin;
              break;
          }

        // Align watermark and set vertical offset
          switch (strtoupper($align_y)) {
            case 'TOP':
              $offset_y = $margin;
              break;
            case 'MIDDLE':
              $offset_y = round(($this->height() - $_watermark->height()) / 2);
              break;
            case 'BOTTOM':
            default:
              $offset_y = $this->height() - $_watermark->height() - $margin;
              break;
          }

        // Create the watermarked image
          $result = ImageCopy($this->_image, $_watermark->_image, $offset_x, $offset_y, 0, 0, $_watermark->width(), $_watermark->height());

        // Free some RAM memory
          ImageDestroy($_watermark->_image);

          return $result;
      }
    }

    public function write($destination, $type=null, $quality=90, $interlaced=false) {

      if (is_file($destination)) {
        trigger_error('Destination already exists: '. $destination, E_USER_WARNING);
        return false;
      }

      if (is_dir($destination)) {
        trigger_error('Destination is a folder: '. $destination, E_USER_WARNING);
        return false;
      }

      if (!is_writable(pathinfo($destination, PATHINFO_DIRNAME))) {
        trigger_error('Destination is not writable: '. $destination .'.', E_USER_WARNING);
        return false;
      }

      if (empty($type)) $type = pathinfo($destination, PATHINFO_EXTENSION);

      if (!in_array(strtolower($type), array('gif', 'jpg', 'png'))) {
        trigger_error('Unknown output format.', E_USER_WARNING);
        return false;
      }

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            trigger_error('Not a valid image object', E_USER_WARNING);
            return false;
          }

          switch(strtolower($type)) {
            case 'jpg':
               $this->_image->setImageCompression(Imagick::COMPRESSION_JPEG);
               break;
            default:
               $this->_image->setImageCompression(Imagick::COMPRESSION_ZIP);
               break;
          }

          $this->_image->setImageCompressionQuality($quality);

          if ($interlaced) $this->_image->setInterlaceScheme(Imagick::INTERLACE_PLANE);

          return $this->_image->writeImage($type.':'.$destination);

        case 'gd':

          if (!is_resource($this->_image)) $this->load();

          if (!is_resource($this->_image)) {
            trigger_error('Not a valid image resource', E_USER_WARNING);
            return false;
          }

          if ($interlaced) ImageInterlace($this->_image, true);

          switch(strtolower($type)) {
            case 'gif':
              $_background = ImageCreateTrueColor(imagesx($this->_image), imagesy($this->_image));
              ImageAlphaBlending($_background, true);
              ImageFill($_background, 0, 0, ImageColorAllocate($_background, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2]));
              ImageCopy($_background, $this->_image, 0, 0, 0, 0, imagesx($this->_image), imagesy($this->_image));
              ImageAlphaBlending($_background, false);
              imagetruecolortopalette($_background, false, 255);
              $result = ImageGIF($_background, $destination);
              ImageDestroy($this->_image);
              ImageDestroy($_background);
              return $result;

            case 'jpeg':
            case 'jpg':
              $_background = ImageCreateTrueColor(imagesx($this->_image), imagesy($this->_image));
              ImageAlphaBlending($_background, true);
              ImageFill($_background, 0, 0, ImageColorAllocateAlpha($_background, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 0));
              ImageCopy($_background, $this->_image, 0, 0, 0, 0, imagesx($this->_image), imagesy($this->_image));
              ImageAlphaBlending($_background, false);
              $result = ImageJPEG($_background, $destination, $quality);
              ImageDestroy($this->_image);
              ImageDestroy($_background);
              return $result;

            case 'png':
              ImageSaveAlpha($this->_image, true);
              $result = ImagePNG($this->_image, $destination);
              ImageDestroy($this->_image);
              return $result;

            default:
              trigger_error('Unknown output format', E_USER_WARNING);
              ImageDestroy($this->_image);
              return false;
          }
      }
    }

    public function output($type='jpg', $quality=90) {

      switch($this->_library) {
        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            trigger_error('Not a valid image object', E_USER_WARNING);
            return false;
          }

          try {
            $this->_image->setImageFormat($type);
            $this->_image->setImageCompressionQuality($quality);
            return $this->_image->getImageBlob();
          } catch (Exception $e) {
            trigger_error($e->getMessage() . ' ($this->_src)', E_USER_WARNING);
            return false;
          }

        case 'gd':

          if (!is_resource($this->_image)) $this->load();

          if (!is_resource($this->_image)) {
            trigger_error('Not a valid image resource', E_USER_WARNING);
            return false;
          }

          switch(strtolower($type)) {
            case 'gif':
              $_background = ImageCreateTrueColor(imagesx($this->_image), imagesy($this->_image));
              ImageAlphaBlending($_background, true);
              ImageFill($_background, 0, 0, ImageColorAllocate($_background, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2]));
              ImageCopy($_background, $this->_image, 0, 0, 0, 0, imagesx($this->_image), imagesy($this->_image));
              ImageAlphaBlending($_background, false);
              imagetruecolortopalette($_background, false, 255);
              $result = ImageGIF($_background, false);
              ImageDestroy($this->_image);
              ImageDestroy($_background);
              return $result;

            case 'jpeg':
            case 'jpg':
              $_background = ImageCreateTrueColor(imagesx($this->_image), imagesy($this->_image));
              ImageAlphaBlending($_background, true);
              ImageFill($_background, 0, 0, ImageColorAllocateAlpha($_background, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 0));
              ImageCopy($_background, $this->_image, 0, 0, 0, 0, imagesx($this->_image), imagesy($this->_image));
              ImageAlphaBlending($_background, false);
              $result = ImageJPEG($_background, false, $quality);
              ImageDestroy($this->_image);
              ImageDestroy($_background);
              return $result;

            case 'png':
              ImageSaveAlpha($this->_image, true);
              $result = ImagePNG($this->_image, false);
              ImageDestroy($this->_image);
              return $result;

            default:
              trigger_error('Unknown output format', E_USER_WARNING);
              ImageDestroy($this->_image);
              return false;
          }
      }
    }

    public function width() {

      if (!empty($this->_width)) return $this->_width;

      if (empty($this->_image) && extension_loaded('gd')) {
        list($this->_width, $this->_height) = getimagesize($this->_src);

        return $this->_width;
      }

      switch($this->_library) {
        case 'imagick':

          if (empty($this->_src)) {
            trigger_error('Not a valid image source set', E_USER_WARNING);
            return false;
          }

          try {
            $this->_image->pingImage($this->_src);
            $this->_width = $this->_image->getImageWidth();
            return $this->_width;
          } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return false;
          }

        case 'gd':

          if (!is_resource($this->_image)) $this->load();

          if (!is_resource($this->_image)) {
            trigger_error('Not a valid image resource', E_USER_WARNING);
            return false;
          }

          $this->_width = ImageSX($this->_image);

          return $this->_width;
      }
    }

    public function height() {

      if (!empty($this->_height)) return $this->_height;

      if (empty($this->_image) && extension_loaded('gd')) {
        list($this->_width, $this->_height) = getimagesize($this->_src);
        return $this->_height;
      }

      switch($this->_library) {
        case 'imagick':

          if (empty($this->_src)) {
            trigger_error('Not a valid image source set', E_USER_WARNING);
            return false;
          }

          try {
            $this->_image->pingImage($this->_src);
            $this->_height = $this->_image->getImageHeight();
            return $this->_height;
          } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return false;
          }

        case 'gd':

          if (!is_resource($this->_image)) $this->load();

          if (!is_resource($this->_image)) {
            trigger_error('Not a valid image resource', E_USER_WARNING);
            return false;
          }
          $this->_height = ImageSY($this->_image);
          return $this->_height;
      }
    }

    public function type() {

      if (!empty($this->_type)) return $this->_type;

      if (empty($this->_image)) {
        if (function_exists('exif_imagetype')) {
          $image_type = exif_imagetype($this->_src);
        } else {
          $params = getimagesize($this->_src);
          $image_type = $params[2];
        }
        switch($image_type) {
          case 1:
            $this->_type = 'gif';
            break;
          case 2:
            $this->_type = 'jpg';
            break;
          case 3:
          default:
            $this->_type = 'png';
            break;
        }
        return $this->_type;
      }

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            trigger_error('Not a valid image object', E_USER_WARNING);
            return false;
          }

          try {
            return $this->_image->getImageFormat();
          } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return false;
          }

        case 'gd':

          if (is_resource($this->_image)) $this->load();

          if (!is_resource($this->_image)) {
            trigger_error('Not a valid image resource', E_USER_WARNING);
            return false;
          }

          return $this->_type;
      }
    }
  }
