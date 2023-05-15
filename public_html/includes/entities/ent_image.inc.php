<?php

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

  class ent_image {
    private $_data = [];
    private $_file = null;
    private $_library = null;
    private $_image = null;
    private $_whitespace = [255, 255, 255];

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

      if (!empty($file)) {
        $this->set($file);
      }

      $this->_whitespace = preg_split('#\s*,\s*#', settings::get('image_whitespace_color'), -1, PREG_SPLIT_NO_EMPTY);
    }

    public function &__get($name) {

      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }

      $this->_data[$name] = null;

      switch ($name) {

        case 'type':

          if (!$this->_image) {
            if (function_exists('exif_imagetype')) {
              $image_type = exif_imagetype($this->_file);
            } else {
              $params = getimagesize($this->_file);
              $image_type = $params[2];
              $this->_data['type'] = 'gif';
            }

            switch ($image_type) {
              case 1: $this->_data['type'] = 'gif'; break 2;
              case 2: $this->_data['type'] = 'jpg'; break 2;
              case 3: $this->_data['type'] = 'png'; break 2;
              case 18: $this->_data['type'] = 'webp'; break 2;

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

          } else {
            $this->load();
          }

          switch ($this->_library) {
            case 'imagick':
              $this->_data['type'] = $this->_image->getImageFormat();
              break 2;

            case 'gd':
              break 2;
          }

          break;

        case 'width':
        case 'height':

          switch ($this->_library) {

            case 'imagick':

            // Prevent DoS attack
              Imagick::setResourceLimit(imagick::RESOURCETYPE_AREA, 24e6);
              Imagick::setResourceLimit(imagick::RESOURCETYPE_MEMORY, 512e6);
              Imagick::setResourceLimit(imagick::RESOURCETYPE_DISK, 512e6);

              if (!$this->_image) $this->load();

              if (!$this->_image) {
                throw new Exception('Not a valid image object');
              }

              try {
                $this->_data['width'] = $this->_image->getImageWidth();
                $this->_data['height'] = $this->_image->getImageHeight();
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

              if ($this->_image) {
                $this->_data['width'] = ImageSX($this->_image);
                $this->_data['height'] = ImageSY($this->_image);
                break 2;
              }

              list($this->_data['width'], $this->_data['height']) = GetImageSize($this->_file);
              break 2;
          }

          break;
      }

      return $this->_data[$name];
    }

    public function &__isset($name) {
      return $this->__get($name);
    }

    public function __set($name, $value) {
      trigger_error("Setting data is prohibited ($name)", E_USER_WARNING);
    }

    public function set($file) {

      $this->_file = null;
      $this->_image = null;
      unset($this->_data['type']);
      unset($this->_data['width']);
      unset($this->_data['height']);

      if (empty($file)) {
        throw new Exception('Could not set image to an empty source file');
      }

    // Handle remote images (Safe for allow_url_fopen = off)
      if (preg_match('#^https?://#', $file)) {

        $client = new http_client();
        $response = $client->call('GET', $file);

        if ($client->last_response['status_code'] != 200) {
          throw new Exception('Remote image location '. $file .' returned an unexpected http response code ('. $client->last_response['status_code'] .')');
        }

        return $this->load_from_string($response);

      } else {
        $file = functions::file_realpath($file);
      }

      if (!is_file($file)) {
        throw new Exception('Could not set image source to a non-existing source ('. $file .')');
      }

      $this->_file = $file;

      return true;
    }

    public function load($file=null) {

      if (!empty($file)) {
        $this->set($file);
      }

      unset($this->_data['type']);
      unset($this->_data['width']);
      unset($this->_data['height']);

      if (empty($this->_file)) {
        throw new Exception('Could not load image from empty source location');
      }

      switch ($this->_library) {

        case 'imagick':

          try {

          // Prevent DoS attack
            Imagick::setResourceLimit(imagick::RESOURCETYPE_AREA, 24e6);
            Imagick::setResourceLimit(imagick::RESOURCETYPE_MEMORY, 128e6);
            Imagick::setResourceLimit(imagick::RESOURCETYPE_DISK, 128e6);

            $this->_image = new Imagick($this->_file);

            return true;

          } catch (\ImagickException $e) {
            throw new Exception("Error loading image ($this->_file)");
          }

          break;

        case 'gd':

          if ($this->width * $this->height > 64e6) {
            throw new Exception('Refused to load image larger than 64 Megapixels');
          }

          switch ($this->type) {
            case 'gif': $this->_image = ImageCreateFromGIF($this->_file); break;
            case 'jpg': $this->_image = ImageCreateFromJPEG($this->_file); break;
            case 'png': $this->_image = ImageCreateFromPNG($this->_file); break;
            case 'webp': $this->_image = ImageCreateFromWebP($this->_file); break;
            case 'svg': return false;
            default: throw new Exception("Cannot load unknown image type ($this->type)");
          }

          if (!$this->_image) {
            throw new Exception("Could not create resource from image ($this->_file)");
          }

          return true;
      }
    }

    public function load_from_string($binary) {

      $tmp_file = functions::file_create_tempfile();
      file_put_contents($tmp_file, $binary);

      $this->load($tmp_file);
    }

    public function resample($width=1024, $height=1024) {

      if (!$this->_image) $this->load();

      if ((int)$width == 0 && (int)$height == 0) return;

      if ((int)$this->width == 0 || (int)$this->height == 0) {
        throw new Exception('Error getting source image dimensions ('. $this->_file .').');
      }

    // Convert percentage dimensions to pixels
      if (substr($width, -1) == '%') {
        $width = round($this->width * str_replace('%', '', $width) / 100);
      }
      if (substr($height, -1) == '%') {
        $height = round($this->height * str_replace('%', '', $height) / 100);
      }

    // Calculate source proportion
      $ratio = $this->width / $this->height;

    // Complete missing target dimensions
      if ((int)$width == 0) {
        $width = round($height * $ratio);
      }
      if ((int)$height == 0) {
        $height = round($width / $ratio);
      }

      switch ($this->_library) {

        case 'imagick':

          try {

            if ($this->type == 'svg') {
              $this->_image->scale($width, $height);
              return;
            }

            $this->_image->setImageBackgroundColor('rgba('.$this->_whitespace[0].','.$this->_whitespace[1].','.$this->_whitespace[2].',0)');

            //$result = $this->_image->scaleImage($width, $height, true);
            //return $this->_image->adaptiveResizeImage($width, $height, true);
            return $this->_image->thumbnailImage($width, $height, true);

          } catch (Exception $e) {
            throw new Exception('Error: Could not resample image: '. $e->getMessage());
          }

          break;

        case 'gd':

          if ($this->type == 'svg') return false;

            // Calculate dimensions
              $new_width = $width;
              $new_height = round($new_width / $ratio);
              if ($new_height > $height) {
                $new_height = $height;
                $new_width = round($new_height * $ratio);
              }

            // Fit only bigger
              if ($new_width > $this->width) {
                $new_width = $this->width;
                $new_height = round($new_width / $ratio);
              }

              if ($new_height > $this->height) {
                $new_height = $this->height;
                $new_width = round($new_height * $ratio);
              }

            // Create output image container
              $_resized = ImageCreateTrueColor($new_width, $new_height);

              //ImageAlphaBlending($_resized, true);
              //ImageSaveAlpha($_resized, true);

              ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

            // Perform resample
              ImageCopyResampled($_resized, $this->_image, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);

              $this->_image = $_resized;
              $this->_data['width'] = $new_width;
              $this->_data['height'] = $new_height;
              return true;

/*
        // Create output image container
          $_resized = ImageCreateTrueColor($new_width, $new_height);

          ImageAlphaBlending($_resized, true);
          ImageSaveAlpha($_resized, true);

          ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

        // Perform resample
          //var_dump($_resized, $this->_image, round(($this->width - $new_width) / 2), round(($this->height - $new_height) / 2), 0, 0, $new_width, $new_height, $this->width, $this->height);exit;
          //$result = ImageCopyResampledFixed($_resized, $this->_image, round(($width - $new_width) / 2), round(($height - $new_height) / 2), 0, 0, $new_width, $new_height, $this->width, $this->height, $this->_whitespace);
          $result = ImageCopyResampledFixed($_resized, $this->_image, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height, $this->_whitespace);

          return $result;
*/
      }
    }

    public function trim() {

      if (!$this->_image) $this->load();

      switch ($this->_library) {

        case 'imagick':

          try {
            $this->_image->trimImage(0);
            $this->resample(round($this->width * 1.15), round($this->height * 1.15), 'FIT_ONLY_BIGGER_USE_WHITESPACING');  // Add 15% padding

          } catch (\ImagickException $e) {
            throw new Exception("Error trimming image ($this->_file)");
          }

          break;

        case 'gd':

          if ($this->type == 'svg') return false;

          //if (function_exists('ImageCropAuto')) { // PHP 5.5
          //  return ImageCropAuto($this->_image, IMG_CROP_SIDES); // Doesn't do it's job properly
          //  return ImageCropAuto($this->_image, IMG_CROP_THRESHOLD, 100, ImageColorAt($this->_image, 0, 0)); // Doesn't do it's job properly
          //}

          $hexcolor = ImageColorAt($this->_image, 0,0);
          $top = $left = 0;
          $right = $original_x = $width = $this->width;
          $bottom = $original_y = $height = $this->height;

          unset($this->_data['width']);
          unset($this->_data['height']);

          do {
          // Top
            for (; $top < $original_y; ++$top) {
              for ($x = 0; $x < $original_x; ++$x) {
                if (imagecolorat($this->_image, $x, $top) != $hexcolor) {
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
                if (imagecolorat($this->_image, $x, $bottom-1) != $hexcolor) {
                  break 2;
                }
              }
            }

          // Left
            for (; $left < $original_x; ++$left) {
              for ($y = $top; $y <= $bottom; ++$y) {
                if (imagecolorat($this->_image, $left, $y) != $hexcolor) {
                  break 2;
                }
              }
            }

          // Right
            for (; $right > 0; --$right) {
              for ($y = $top; $y <= $bottom; ++$y) {
                if (imagecolorat($this->_image, $right-1, $y) != $hexcolor) {
                  break 2;
                }
              }
            }

            $width = $right - $left;
            $height = $bottom - $top;
            $code = ($width < $original_x || $height < $original_y) ? 1 : 0;
          } while (0);

          //$padding = 50; // Set padding size in px
          $padding = round($width * 0.15); // Set padding size in percentage

          $_image = ImageCreateTrueColor($width + $padding * 2, $height + $padding * 2);
          ImageAlphaBlending($_image, true);
          ImageFill($_image, 0, 0, ImageColorAllocateAlpha($_image, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 0));

          $result = ImageCopy($_image, $this->_image, $padding, $padding, $left, $top, $width, $height);

          if ($result) {
            ImageDestroy($this->_image);
            $this->_image = $_image;
            $this->_data['width'] = $width + ($padding*2);
            $this->_data['height'] = $height + ($padding*2);
          }

          return $result;
      }
    }

    public function watermark($watermark, $align_x='RIGHT', $align_y='BOTTOM', $margin=5) {

      if (!$this->_image) $this->load();

      if (!is_file($watermark)) {
        throw new Exception("Cannot load watermark as file is missing ($watermark)");
      }

      switch ($this->_library) {

        case 'imagick':

          try {

            $_watermark = new imagick();
            $_watermark->readImage($watermark);

          if ($_watermark->getImageWidth() > ($this->width() / 5) || $_watermark->getImageHeight() > ($this->height() / 5)) {
            $_watermark->thumbnailImage(round($this->width()/5), round($this->height()/5), true);
          }

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

            return $this->_image->compositeImage($_watermark, imagick::COMPOSITE_OVER, $offset_x, $offset_y);

          } catch (\ImagickException $e) {
            throw new Exception("Error applying watermark ($watermark)");
          }

          break;

        case 'gd':

          if ($this->type == 'svg') return false;

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
          $_watermark->resample(round($this->width()/5), round($this->height()/5), 'FIT_ONLY_BIGGER');

        // Align watermark and set horizontal offset
          switch (strtoupper($align_x)) {
            case 'LEFT':
              $offset_x = $margin;
              break;
            case 'CENTER':
              $offset_x = round(($this->width - $_watermark->width) / 2);
              break;
            case 'RIGHT':
            default:
              $offset_x = $this->width - $_watermark->width - $margin;
              break;
          }

        // Align watermark and set vertical offset
          switch (strtoupper($align_y)) {
            case 'TOP':
              $offset_y = $margin;
              break;
            case 'MIDDLE':
              $offset_y = round(($this->height - $_watermark->height) / 2);
              break;
            case 'BOTTOM':
            default:
              $offset_y = $this->height - $_watermark->height - $margin;
              break;
          }

        // Create the watermarked image
          $result = ImageCopy($this->_image, $_watermark->_image, $offset_x, $offset_y, 0, 0, $_watermark->width, $_watermark->height);

        // Free some RAM memory
          ImageDestroy($_watermark->_image);

          return $result;
      }
    }

    public function write($destination, $quality=90, $interlaced=false) {

      $destination = functions::file_realpath($destination);

      if (!$this->_image) $this->load();

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

      if (!preg_match('#^(gif|jpe?g|png|webp)$#i', $type)) {
        throw new Exception("Unknown image format ($type)");
      }

      switch ($this->_library) {

        case 'imagick':

          if ($this->_image->getImageDepth() > 16) {
            $this->_image->setImageDepth(16);
          }

          if (strtolower($type) == 'jpg') {
             $this->_image->setImageCompression(Imagick::COMPRESSION_JPEG);
          } else {
             $this->_image->setImageCompression(Imagick::COMPRESSION_ZIP);
          }

          $this->_image->setImageCompressionQuality((int)$quality);

          if ($interlaced) {
            $this->_image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
          }

          return $this->_image->writeImage($type.':'.$destination);

          break;

        case 'gd':

          if ($this->type == 'svg') {
            if ($type != 'svg') {
              throw new Exception("GD2 does not support converting .svg to .$type. Enable Imagick for PHP instead");
            } else {
              return copy($this->_file, $destination);
            }
          }

          if ($type == 'webp' && !function_exists('ImageWebP')) {
            return $this->write(preg_replace('#\.webp$#', '.jpg', $destination), $quality, $interlaced);
          }

          $new_image = ImageCreateTrueColor($this->width, $this->height);

          if (in_array($type, ['png', 'webp'])) {
            ImageAlphaBlending($new_image, false);
            ImageSaveAlpha($new_image, true);
          }

          ImageFill($new_image, 0, 0, ImageColorAllocateAlpha($new_image, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 0));

          ImageCopy($new_image, $this->_image, 0, 0, 0, 0, $this->width, $this->height);

          if (in_array($type, ['gif'])) {
            ImageTrueColorToPalette($new_image, false, 255);
          }

          if ($interlaced) {
            ImageInterlace($new_image, true);
          }

          switch ($type) {
            case 'gif': $result = ImageGIF($new_image, $destination); break;
            case 'jpg': $result = ImageJPEG($new_image, $destination, $quality); break;
            case 'png': $result = ImagePNG($new_image, $destination); break;
            case 'webp': $result = ImageWebP($new_image, $destination, $quality); break;
            default: throw new Exception('Unknown output format');
          }

          ImageDestroy($new_image);
          return $result;
      }
    }
  }
