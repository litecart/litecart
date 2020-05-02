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

  class ent_image {
    private $_data = array();
    private $_file = null;
    private $_library = null;
    private $_resource = null;
    private $_whitespace = null;

    public function __construct($file=null, $library=null) {

    // Set library
      if (!empty($library)) {
        $this->_library = $library;

      } else if (extension_loaded('imagick')) {
        $this->_library = 'imagick';

      } else if (extension_loaded('gd')) {
        $this->_library = 'gd';

      } else {
        throw new Exception('No image processing library available');
      }

      if (!empty($file)) $this->_file = $file;

      $this->_whitespace = explode(',', settings::get('image_whitespace_color'));
    }

    public function &__get($name) {

      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }

      $this->_data[$name] = null;

      switch ($name) {
        case 'type':

          if (empty($this->_resource)) {
            if (function_exists('exif_imagetype')) {
              $image_type = exif_imagetype($this->_file);
            } else {
              $params = getimagesize($this->_file);
              $image_type = $params[2];
            }

            switch ($image_type) {
              case 1:
                $this->_data['type'] = 'gif';
                break 2;

              case 2:
                $this->_data['type'] = 'jpg';
                break 2;

              case 3:
                $this->_data['type'] = 'png';
                break 2;

              case 18:
                $this->_data['type'] = 'webp';
                break 2;

              case false:
                if (strpos(file_get_contents($this->_file, false, null, 0, 256), '<svg') !== false) {
                  $this->_data['type'] = 'svg';
                  break 2;
                }
                break;

              default:
              // Set PNG for other graphics formats
                $this->_data['type'] = 'png';
                break 2;
            }
          }

          switch ($this->_library) {

            case 'imagick':

              if (empty($this->_resource)) $this->load();

              if (empty($this->_resource)) {
                throw new Exception('Not a valid image object');
              }

              return $this->_resource->getImageFormat();

            case 'gd':

              if (is_resource($this->_resource)) $this->load();

              if (!is_resource($this->_resource)) {
                throw new Exception('Not a valid image resource');
              }

              break 2;
          }

          break;

        case 'width':
        case 'height':

          switch ($this->_library) {

            case 'imagick':

              if (empty($this->_resource)) $this->load();

              if (empty($this->_resource)) {
                throw new Exception('Not a valid image object');
              }

              try {
                $this->_data['width'] = $this->_resource->getImageWidth();
                $this->_data['height'] = $this->_resource->getImageHeight();
              } catch (\ImagickException $e) {
                throw new Exception("Error getting source image dimensions ($this->_file)");
              }

              break 2;

            case 'gd':

              if ($this->type == 'svg') {
                $this->_data['width'] = 0;
                $this->_data['height'] = 0;
                break 2;
              }

              if (is_resource($this->_resource)) {
                $this->_data['width'] = ImageSX($this->_resource);
                $this->_data['height'] = ImageSY($this->_resource);
                break 2;
              }

              list($this->_data['width'], $this->_data['height']) = GetImageSize($this->_file);
              break 2;
          }
      }

      return $this->_data[$name];
    }

    public function &__isset($name) {
      return $this->__get($name);
    }

    public function __set($name, $value) {
      trigger_error("Setting data is prohibited ($name)", E_USER_WARNING);
    }

    public function get_resource() {
      return $this->_resource;
    }

    public function load($file=null) {

      if (!empty($file)) {
        if (!is_file($file)) {
          throw new Exception("Cannot load a missing image file ($file)");
        }
        $this->_file = $file;
      }

      if (empty($this->_file)) {
        throw new Exception('Cannot load a missing file as no file has been set');
      }

      switch ($this->_library) {

        case 'imagick':

          try {
            $this->_resource = new Imagick($this->_file);
            return true;

          } catch (\ImagickException $e) {
            throw new Exception("Error loading image ($this->_file)");
          }

          break;

        case 'gd':

          switch ($this->type) {

            case 'gif':
              $this->_resource = ImageCreateFromGIF($this->_file);
              break;

            case 'jpg':
              $this->_resource = ImageCreateFromJPEG($this->_file);
              break;

            case 'png':
              $this->_resource = ImageCreateFromPNG($this->_file);
              break;

            case 'webp':
              $this->_resource = ImageCreateFromWebP($this->_file);
              break;

            case 'svg':
              return false;

            default:
              throw new Exception("Cannot load unknown image type ($this->type)");
          }

          if (!is_resource($this->_resource)) {
            throw new Exception("Could not create resource from image ($this->_file)");
          } else {
            return true;
          }
      }
    }

    public function load_from_string($binary) {

      $this->_data['type'] = strtolower($type);
      unset($this->_data['width']);
      unset($this->_data['height']);

      $tmpfile = tmpfile();

      file_put_contents($tmpfile, $binary);

      return $this->load($tmpfile);
    }

    public function resample($width=1024, $height=1024, $clipping='FIT_ONLY_BIGGER') {

      if ($width == 0 && $height == 0) return;

      if ($this->width === 0 || $this->height === 0) return;

    // Convert percentage dimensions to pixels
      if (strpos($width, '%')) $width = $this->width * str_replace('%', '', $width) / 100;
      if (strpos($height, '%')) $height = $this->height * str_replace('%', '', $height) / 100;

    // Calculate source proportion
      $source_ratio = $this->width / $this->height;

    // Complete missing target dimensions
      if ($width == 0) $width = round($height * $source_ratio);
      if ($height == 0) $height = round($width / $source_ratio);

      switch ($this->_library) {

        case 'imagick':

          if (empty($this->_resource)) $this->load();

          try {

            if ($this->type == 'svg') {
              $this->_resource->scale($width, $height);
              return;
            }

            $this->_resource->setImageBackgroundColor('rgba('.$this->_whitespace[0].','.$this->_whitespace[1].','.$this->_whitespace[2].',0)');

            switch (strtoupper($clipping)) {

              case 'FIT':
                //$result = $this->_resource->scaleImage($width, $height, true);
                //return $this->_resource->adaptiveResizeImage($width, $height, true);
                return $this->_resource->thumbnailImage($width, $height, true);

              case 'FIT_ONLY_BIGGER':
                if ($this->width <= $width && $this->height <= $height) return true;
                return $this->_resource->thumbnailImage($width, $height, true);

              case 'FIT_USE_WHITESPACING':
                return $this->_resource->thumbnailImage($width, $height, true, true);

              case 'FIT_ONLY_BIGGER_USE_WHITESPACING':
                if ($this->width <= $width && $this->height <= $height) {
                  $_newimage = new imagick();
                  $_newimage->newImage($width, $height, 'rgba('.$this->_whitespace[0].','.$this->_whitespace[1].','.$this->_whitespace[2].',0)');
                  $offset_x = round(($width - $this->width) / 2);
                  $offset_y = round(($height - $this->height) / 2);
                  $result = $_newimage->compositeImage($this->_resource, imagick::COMPOSITE_OVER, $offset_x, $offset_y);
                  $this->_resource = $_newimage;
                  return $result;
                }

                return $this->_resource->thumbnailImage($width, $height, true, true);

              case 'CROP':

                return $this->_resource->cropThumbnailImage($width, $height);

              case 'CROP_ONLY_BIGGER':
                if ($this->width <= $width && $this->height <= $height) return true;
                return $this->_resource->cropThumbnailImage($width, $height);

              case 'STRETCH':
                //return $this->_resource->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1); // Stretch
                //return $this->_resource->adaptiveResizeImage($width, $height, false); // Stretch
                return $this->_resource->thumbnailImage($width, $height, false); // Stretch

              default:
                throw new Exception('Unknown clipping method ($clipping)');
            }

          } catch (\ImagickException $e) {
            throw new Exception("Error resampling image ($this->_file)");
          }

          break;

        case 'gd':

          if ($this->type == 'svg') return false;

          if (!is_resource($this->_resource)) $this->load();

          switch (strtoupper($clipping)) {

            case 'CROP':
            case 'CROP_ONLY_BIGGER':

            // Calculate dimensions
              $destination_width = $width;
              $destination_height = $height;

              if (strtoupper($clipping) == 'CROP_ONLY_BIGGER') {
                if ($this->width < $destination_width) {
                  $destination_width = $this->width;
                }
                if ($this->height < $destination_height) {
                  $destination_height = $this->height;
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
              if (($this->width / $destination_width) > ($this->height / $destination_height)) {
                $result = ImageCopyResampledFixed($_resized, $this->_resource, 0, 0, ($this->width - $destination_width * $this->height / $destination_height) / 2, 0, $destination_width, $destination_height, $this->height * $destination_ratio, $this->height, $this->_whitespace);
              } else {
                $result = ImageCopyResampledFixed($_resized, $this->_resource, 0, 0, 0, ($this->height - $destination_height * $this->width / $destination_width) / 2, $destination_width, $destination_height, $this->width, $this->width / $destination_ratio, $this->_whitespace);
              }

              break;

            case 'STRETCH':

            // Calculate dimensions
              $destination_width = ($width == 0) ? $this->width : $width;
              $destination_height = ($height == 0) ? $this->height : $height;

            // Create output image container
              $_resized = ImageCreateTrueColor($destination_width, $destination_height);

              ImageAlphaBlending($_resized, true);
              ImageSaveAlpha($_resized, true);

              ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

            // Perform resample
              $result = ImageCopyResampledFixed($_resized, $this->_resource, ($width - $destination_width) / 2, ($height - $destination_height) / 2, 0, 0, $destination_width, $destination_height, $this->width, $this->height, $this->_whitespace);

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

              if (in_array(strtoupper($clipping), ['FIT_ONLY_BIGGER', 'FIT_ONLY_BIGGER_USE_WHITESPACING'])) {
                if ($destination_width > $destination_height) {
                  if ($destination_width > $this->width) {
                    $destination_width = $this->width;
                    $destination_height = round($destination_width / $source_ratio);
                  }
                } else {
                  if ($destination_height > $this->height) {
                    $destination_height = $this->height;
                    $destination_width = round($destination_height * $source_ratio);
                  }
                }
              }

              if (in_array(strtoupper($clipping), ['FIT_USE_WHITESPACING', 'FIT_ONLY_BIGGER_USE_WHITESPACING'])) {

              // Create output image container
                $_resized = ImageCreateTrueColor($width, $height);

                ImageAlphaBlending($_resized, true);
                ImageSaveAlpha($_resized, true);

              // Fill with whitespace color
                ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

              // Make whitespace color transparent
                //ImageColorTransparent($_resized, ImageColorAllocate($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2]));

              // Perform resample
                $result = ImageCopyResampled($_resized, $this->_resource, ($width - $destination_width) / 2, ($height - $destination_height) / 2, 0, 0, $destination_width, $destination_height, $this->width, $this->height);

              } else {

              // Create output image container
                $_resized = ImageCreateTrueColor($destination_width, $destination_height);

                ImageAlphaBlending($_resized, true);
                ImageSaveAlpha($_resized, true);

                ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

              // Perform resample
                $result = ImageCopyResampledFixed($_resized, $this->_resource, ($width - $destination_width) / 2, ($height - $destination_height) / 2, 0, 0, $destination_width, $destination_height, $this->width, $this->height, $this->_whitespace);
              }

              break;

            default:
              throw new Exception('Unknown clipping method');
          }

          $this->_data['width'] = $destination_width;
          $this->_data['height'] = $destination_height;

          ImageDestroy($this->_resource);
          $this->_resource = $_resized;

          return $result;
      }
    }

    public function filter($filter) {

      switch ($this->_library) {

        case 'imagick':

          if (empty($this->_resource)) $this->load();

          try {

            switch ($filter) {

              case 'blur':
                //return $this->_resource->gaussianBlurImage(2, 3);
                return $this->_resource->blurImage(2, 3);

              case 'contrast':
                return $this->_resource->contrastImage(2);

              case 'gamma':
                return $this->_resource->gammaImage(1.25);

              case 'pixelate':
                $width = $this->_resource->getImageWidth();
                $this->_resource->scaleImage($width/10, 0);
                return $this->_resource->scaleImage($width, 0);

              case 'sepia':
                return $this->_resource->sepiaToneImage(80);

              case 'sharpen':
                return $this->_resource->sharpenImage(2, 3);

              default:
                throw new Exception('Unknown filter effect for image');
            }

          } catch (\ImagickException $e) {
            throw new Exception("Error applying filter on image ($this->_file)");
          }

          break;

        case 'gd':

          if ($this->type == 'svg') return false;

          if (!is_resource($this->_resource)) $this->load();

          switch ($filter) {

            case 'contrast':
              ImageFilter($this->_resource, IMG_FILTER_CONTRAST, -2);
              return true;

            case 'gamma':
              ImageGammaCorrect($this->_resource, 1.0, 1.25);
              return true;

            case 'blur':
              ImageFilter($this->_resource, IMG_FILTER_GAUSSIAN_BLUR);
              //ImageFilter($this->_resource, IMG_FILTER_SELECTIVE_BLUR);
              return true;

            case 'pixelate':
              $blocksize = 12;
              for ($x = 0; $x < $this->width; $x += $blocksize) {
                for ($y = 0; $y < $this->height; $y += $blocksize) {
                  $rgb = ImageColorAt($this->_resource, $x, $y);
                  ImageFilledRectangle($this->_resource, $x, $y, $x + $blocksize - 1, $y + $blocksize - 1, $rgb);
                }
              }
              return true;

            case 'sepia':
              ImageFilter($this->_resource, IMG_FILTER_GRAYSCALE);
              ImageFilter($this->_resource, IMG_FILTER_COLORIZE, 100, 50, 0);
              return true;

            case 'sharpen':
              $matrix = [[-1,-1,-1], [-1,16,-1], [-1,-1,-1]];
              $divisor = array_sum(array_map('array_sum', $matrix));
              $offset = 0;
              ImageConvolution($this->_resource, $matrix, $divisor, $offset);
              return true;

            default:
              throw new Exception('Unknown filter effect for image');
          }

        break;
      }
    }

    public function trim() {

      switch ($this->_library) {

        case 'imagick':

          if (empty($this->_resource)) $this->load();

          try {
            $this->_resource->trimImage(0);
            $this->resample($this->width * 1.15, $this->height * 1.15, 'FIT_ONLY_BIGGER_USE_WHITESPACING');  // Add 15% padding

          } catch (\ImagickException $e) {
            throw new Exception("Error applying filter on image ($this->_file)");
          }

          break;

        case 'gd':

          if ($this->type == 'svg') return false;

          if (!is_resource($this->_resource)) $this->load();

          //if (function_exists('ImageCropAuto')) { // PHP 5.5
          //  return ImageCropAuto($this->_resource, IMG_CROP_SIDES); // Doesn't do it's job properly
          //  return ImageCropAuto($this->_resource, IMG_CROP_THRESHOLD, 100, imagecolorat($this->_resource, 0, 0)); // Doesn't do it's job properly
          //}

          $hexcolor = ImagecColorAt($this->_resource, 0,0);
          $top = $left = 0;
          $right = $original_x = $width = $this->width;
          $bottom = $original_y = $height = $this->height;

          unset($this->_data['width']);
          unset($this->_data['height']);

          do {
          // Top
            for (; $top < $original_y; ++$top) {
              for ($x = 0; $x < $original_x; ++$x) {
                if (@imagecolorat($this->_resource, $x, $top) != $hexcolor) {
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
                if (@imagecolorat($this->_resource, $x, $bottom-1) != $hexcolor) {
                  break 2;
                }
              }
            }

          // Left
            for (; $left < $original_x; ++$left) {
              for ($y = $top; $y <= $bottom; ++$y) {
                if (@imagecolorat($this->_resource, $left, $y) != $hexcolor) {
                  break 2;
                }
              }
            }

          // Right
            for (; $right > 0; --$right) {
              for ($y = $top; $y <= $bottom; ++$y) {
                if (@imagecolorat($this->_resource, $right-1, $y) != $hexcolor) {
                  break 2;
                }
              }
            }

            $width = $right - $left;
            $height = $bottom - $top;
            $code = ($width < $original_x || $height < $original_y) ? 1 : 0;
          } while (0);

          //$padding = 50; // Set padding size in px
          $padding = $width * 0.15; // Set padding size in percentage

          $_image = ImageCreateTrueColor($width + ($padding * 2), $height + ($padding * 2));
          ImageAlphaBlending($_image, true);
          ImageFill($_image, 0, 0, ImageColorAllocateAlpha($_image, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 0));

          $result = ImageCopy($_image, $this->_resource, $padding, $padding, $left, $top, $width, $height);

          if ($result) {
            ImageDestroy($this->_resource);
            $this->_resource = $_image;
            $this->_data['width'] = $width + ($padding*2);
            $this->_data['height'] = $height + ($padding*2);
          }

          return $result;
      }
    }

    public function watermark($watermark, $align_x='RIGHT', $align_y='BOTTOM', $margin=5) {

      if (!is_file($watermark)) {
        throw new Exception("Cannot load watermark as file is missing ($watermark)");
      }

      switch ($this->_library) {

        case 'imagick':

          if (empty($this->_resource)) $this->load();

          try {
            $_watermark = new imagick();
            $_watermark->readImage($watermark);

            switch (strtoupper($align_x)) {
              case 'LEFT':
                $offset_x = $margin;
                break;
              case 'CENTER':
                $offset_x = round(($this->width - $_watermark->getImageWidth()) / 2);
                break;
              case 'RIGHT':
              default:
                $offset_x = $this->width - $_watermark->getImageWidth() - $margin;
                break;
            }

            switch (strtoupper($align_y)) {
              case 'TOP':
                $offset_y = $margin;
                break;
              case 'CENTER':
              case 'MIDDLE':
                $offset_y = round(($this->height - $_watermark->getImageHeight()) / 2);
                break;
              case 'BOTTOM':
              default:
                $offset_y = $this->height - $_watermark->getImageHeight() - $margin;
                break;
            }

            return $this->_resource->compositeImage($_watermark, imagick::COMPOSITE_OVER, $offset_x, $offset_y);

          } catch (\ImagickException $e) {
            throw new Exception("Error applying watermark ($watermark)");
          }

          break;

        case 'gd':

          if ($this->type == 'svg') return false;

          if (!is_resource($this->_resource)) $this->load();

          $_watermark = new ent_image($watermark, $this->_library);

        // Return false on no image
          if (!$_watermark->type()) {
            throw new Exception("Watermark file is not a valid image ($watermark)");
          }

        // Load watermark
          $_watermark->load();

        // Check if watermark is a PNG file
          if ($_watermark->type() != 'png') {
            trigger_error("Watermark file is not a PNG image ($watermark)", E_USER_NOTICE);
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
          $result = ImageCopy($this->_resource, $_watermark->get_resource(), $offset_x, $offset_y, 0, 0, $_watermark->width(), $_watermark->height());

        // Free some RAM memory
          ImageDestroy($_watermark->_resource);

          return $result;
      }
    }

    public function write($destination, $quality=90, $interlaced=false) {

      if (is_file($destination)) {
        throw new Exception("Destination already exists ($destination)");
      }

      if (is_dir($destination)) {
        throw new Exception("Destination is a folder ($destination)");
      }

      if (!is_writable(pathinfo($destination, PATHINFO_DIRNAME))) {
        throw new Exception("Destination is not writable ($destination)");
      }

      $type = strtolower(pathinfo($destination, PATHINFO_EXTENSION));

      if (!in_array($type, ['gif', 'jpg', 'png', 'svg', 'webp'])) {
        throw new Exception('Unknown image format');
      }

      switch ($this->_library) {

        case 'imagick':

          if (empty($this->_resource)) $this->load();

          try {
            switch (strtolower($type)) {
              case 'jpg':
                 $this->_resource->setImageCompression(Imagick::COMPRESSION_JPEG);
                 break;

              case 'webp':
                 $this->_resource->setImageCompression(Imagick::COMPRESSION_ZIP);
                 break;

              default:
                 $this->_resource->setImageCompression(Imagick::COMPRESSION_ZIP);
                 break;
            }

            $this->_resource->setImageCompressionQuality($quality);

            if ($interlaced) $this->_resource->setInterlaceScheme(Imagick::INTERLACE_PLANE);

            return $this->_resource->writeImage($type.':'.$destination);

          } catch (\ImagickException $e) {
            throw new Exception("Error applying watermark ($watermark)");
          }

          break;

        case 'gd':

          if (!is_resource($this->_resource)) $this->load();

          if ($this->type == 'svg') {
            if ($type != 'svg') {
              throw new Exception("GD2 does not support converting .svg to .$type");
            } else {
              return copy($this->_file, $destination);
            }
          }

          if ($interlaced) ImageInterlace($this->_resource, true);

          switch (strtolower($type)) {
            case 'gif':
              $_background = ImageCreateTrueColor(imagesx($this->_resource), imagesy($this->_resource));
              ImageAlphaBlending($_background, true);
              ImageFill($_background, 0, 0, ImageColorAllocate($_background, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2]));
              ImageCopy($_background, $this->_resource, 0, 0, 0, 0, imagesx($this->_resource), imagesy($this->_resource));
              ImageAlphaBlending($_background, false);
              imagetruecolortopalette($_background, false, 255);
              $result = ImageGIF($_background, $destination);
              ImageDestroy($this->_resource);
              ImageDestroy($_background);
              return $result;

            case 'jpeg':
            case 'jpg':
              $_background = ImageCreateTrueColor(imagesx($this->_resource), imagesy($this->_resource));
              ImageAlphaBlending($_background, true);
              ImageFill($_background, 0, 0, ImageColorAllocateAlpha($_background, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 0));
              ImageCopy($_background, $this->_resource, 0, 0, 0, 0, imagesx($this->_resource), imagesy($this->_resource));
              ImageAlphaBlending($_background, false);
              $result = ImageJPEG($_background, $destination, $quality);
              ImageDestroy($this->_resource);
              ImageDestroy($_background);
              return $result;

            case 'png':
              ImageSaveAlpha($this->_resource, true);
              $result = ImagePNG($this->_resource, $destination);
              ImageDestroy($this->_resource);
              return $result;

            case 'webp':
              if (!function_exists('ImageWebP')) {
                return $this->write(preg_replace('#\.webp$#', '.jpg', $destination), $quality, $interlaced);
              }
              ImageSaveAlpha($this->_resource, true);
              $result = ImageWebP($this->_resource, $destination, $quality);
              ImageDestroy($this->_resource);
              return $result;

            default:
              ImageDestroy($this->_resource);
              throw new Exception('Unknown output format');
          }

          break;
      }
    }
  }
