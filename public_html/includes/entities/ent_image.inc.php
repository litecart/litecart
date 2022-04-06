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
    private $_library;
    private $_image;
    private $_src;
    private $_type;
    private $_width;
    private $_height;
    private $_whitespace = [255, 255, 255];

    public function __construct($file=null, $force_library=null) {

    // Set library
      if (!empty($force_library)) {
        $this->_library = $force_library;
      } else if (extension_loaded('imagick')) {
        $this->_library = 'imagick';
      } else if (extension_loaded('gd')) {
        $this->_library = 'gd';
      } else {
        throw new Exception('No image processing library available');
      }

      $this->_whitespace = preg_split('#\s*,\s*#', settings::get('image_whitespace_color'), -1, PREG_SPLIT_NO_EMPTY);

      if (!empty($file)) {
        $this->set($file);
      }
    }

    public function set($file) {

      $this->_src = null;
      $this->_image = null;
      $this->_width = null;
      $this->_height = null;
      $this->_type = null;

      if (empty($file)) {
        throw new Exception('Could not set image to an empty source file');
      }

    // Handle remote images (Safe for allow_url_fopen = off)
      if (preg_match('#^https?://#', $file)) {

        $client = new wrap_http();
        $response = $client->call('GET', $file);

        if ($client->last_response['status_code'] != 200) {
          throw new Exception('Remote image location '. $file .' returned an unexpected http response code ('. $client->last_response['status_code'] .')');
        }

        $tmpfile = tempnam(sys_get_temp_dir(), '') .'.'. pathinfo(parse_url($file, PHP_URL_PATH), PATHINFO_EXTENSION);
        file_put_contents($tmpfile, $response);

        $this->_src = $tmpfile;
        return true;
      }

      if (!is_file($file)) {
        throw new Exception('Could not set image source to a non-existing source');
      }

      $this->_src = $file;
      return true;
    }

    public function load($file=null) {

      if (!empty($file)) {
        $this->set($file);
      }

      if (empty($this->_src)) {
        throw new Exception('Could not load image from empty source location');
      }

      switch($this->_library) {

        case 'imagick':

          // Prevent DoS attack
            Imagick::setResourceLimit(imagick::RESOURCETYPE_AREA, 24e6);
            Imagick::setResourceLimit(imagick::RESOURCETYPE_MEMORY, 128e6);
            Imagick::setResourceLimit(imagick::RESOURCETYPE_DISK, 128e6);

            $this->_image = new imagick($this->_src);

            return true;

        case 'gd':

          if ($this->width() * $this->height() > 64e6) {
            throw new Exception('Refused to load image larger than 64 Megapixels');
          }

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

            case 'webp':
              $this->_type = 'webp';
              $this->_image = ImageCreateFromWebP($this->_src);
              break;

            default:
              $this->load_from_string(file_get_contents($this->_src));
              break;
          }

          return $this->_image ? true : false;
      }
    }

    public function load_from_string($binary) {

      $this->_image = null;
      $this->_type = null;
      $this->_width = null;
      $this->_height = null;

      switch($this->_library) {

        case 'imagick':

        // Prevent DoS attack
          Imagick::setResourceLimit(imagick::RESOURCETYPE_AREA, 24e6);
          Imagick::setResourceLimit(imagick::RESOURCETYPE_MEMORY, 128e6);
          Imagick::setResourceLimit(imagick::RESOURCETYPE_DISK, 128e6);

          $this->_image = new imagick();

          $this->_image->readImageBlob($binary);
          $this->_type = $this->_image->getImageFormat();

          return true;

        case 'gd':

          if (!$info = GetImageSizeFromString($binary)) {
            throw new Exception('Could not extract image type and dimensions from binary string data');
          }

          $this->_width = $info[0];
          $this->_height = $info[1];

          switch($info[2]) {
            case 1:
              $this->_type = 'gif';
              break;
            case 2:
              $this->_type = 'jpg';
              break;
            case 18:
              $this->_type = 'webp';
              break;
            case 3:
            default:
              $this->_type = 'png';
              break;
          }

          if ($this->width() * $this->height() > 64e6) {
            throw new Exception('Refused to load image larger than 64 Megapixels');
          }

          $this->_image = ImageCreateFromString($binary);

          return true;
      }
    }

    public function resample($width=1024, $height=1024, $clipping='FIT_ONLY_BIGGER') {

      if ((int)$width == 0 && (int)$height == 0) return;

      if ((int)$this->width() == 0 || (int)$this->height() == 0) {
        throw new Exception('Error getting source image dimensions ('. $this->_src .').');
      }

    // Convert percentage dimensions to pixels
      if (strpos($width, '%')) $width = round($this->width() * str_replace('%', '', $width) / 100);
      if (strpos($height, '%')) $height = round($this->height() * str_replace('%', '', $height) / 100);

    // Calculate source proportion
      $source_ratio = $this->width() / $this->height();

    // Complete missing target dimensions
      if ((int)$width == 0) $width = round($height * $source_ratio);
      if ((int)$height == 0) $height = round($width / $source_ratio);

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            throw new Exception('Not a valid image object');
          }

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
                $result = $_newimage->compositeImage($this->_image, imagick::COMPOSITE_COPY, $offset_x, $offset_y);
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
              throw new Exception('Unknown clipping method ($clipping)');
        }

        case 'gd':

          if (!$this->_image) $this->load();

          if (!$this->_image) {
            throw new Exception('Not a valid image resource');
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

              ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

            // Perform resample
              if (($this->width() / $destination_width) > ($this->height() / $destination_height)) {
                ImageCopyResampledFixed($_resized, $this->_image, 0, 0, round(($this->width() - $destination_width * $this->height() / $destination_height) / 2), 0, $destination_width, $destination_height, round($this->height() * $destination_ratio), $this->height(), $this->_whitespace);
              } else {
                ImageCopyResampledFixed($_resized, $this->_image, 0, 0, 0, round(($this->height() - $destination_height * $this->width() / $destination_width) / 2), $destination_width, $destination_height, $this->width(), round($this->width() / $destination_ratio), $this->_whitespace);
              }

              break;

            case 'STRETCH':

            // Calculate dimensions
              $destination_width = ((int)$width == 0) ? $this->width() : $width;
              $destination_height = ((int)$height == 0) ? $this->height() : $height;

            // Create output image container
              $_resized = ImageCreateTrueColor($destination_width, $destination_height);

              ImageAlphaBlending($_resized, true);
              ImageSaveAlpha($_resized, true);

              ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

            // Perform resample
              ImageCopyResampledFixed($_resized, $this->_image, round(($width - $destination_width) / 2), round(($height - $destination_height) / 2), 0, 0, $destination_width, $destination_height, $this->width(), $this->height(), $this->_whitespace);

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
                ImageCopyResampled($_resized, $this->_image, round(($width - $destination_width) / 2), round(($height - $destination_height) / 2), 0, 0, $destination_width, $destination_height, $this->width(), $this->height());

              } else {

              // Create output image container
                $_resized = ImageCreateTrueColor($destination_width, $destination_height);

                ImageAlphaBlending($_resized, true);
                ImageSaveAlpha($_resized, true);

                ImageFill($_resized, 0, 0, ImageColorAllocateAlpha($_resized, $this->_whitespace[0], $this->_whitespace[1], $this->_whitespace[2], 127));

              // Perform resample
                ImageCopyResampledFixed($_resized, $this->_image, round(($width - $destination_width) / 2), round(($height - $destination_height) / 2), 0, 0, $destination_width, $destination_height, $this->width(), $this->height(), $this->_whitespace);
              }

              break;

            default:
              throw new Exception('Unknown clipping method');
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
            throw new Exception('Not a valid image object');
          }

          switch($filter) {
            case 'blur':
              //return $this->_image->gaussianBlurImage(2, 3);
              return $this->_image->blurImage(2, 3);

            case 'contrast':
              return $this->_image->contrastImage(2);

            case 'gamma':
              return $this->_image->gammaImage(1.25);

            case 'pixelate':
              $width = $this->_image->getImageWidth();
              $this->_image->scaleImage($width/10, 0);
              return $this->_image->scaleImage($width, 0);

            case 'sepia':
              return $this->_image->sepiaToneImage(80);

            case 'sharpen':
              return $this->_image->sharpenImage(2, 3);

            default:
              throw new Exception('Unknown filter effect for image');
          }

        case 'gd':

          if (!$this->_image) $this->load();

          if (!$this->_image) {
            throw new Exception('Not a valid image resource');
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
              $matrix = [[-1,-1,-1], [-1,16,-1], [-1,-1,-1]];
              $divisor = array_sum(array_map('array_sum', $matrix));
              $offset = 0;
              ImageConvolution($this->_image, $matrix, $divisor, $offset);
              return true;

            default:
              throw new Exception('Unknown filter effect for image');
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
            throw new Exception('Not a valid image object');
          }

          if (!$this->_image->trimImage(0)) return false;

          $this->resample(round($this->width() * 1.15), round($this->height() * 1.15), 'FIT_ONLY_BIGGER_USE_WHITESPACING');  // Add 15% padding

          return true;

        case 'gd':

          if (!$this->_image) $this->load();

          if (!$this->_image) {
            throw new Exception('Not a valid image resource');
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
            $this->_width = $width + $padding * 2;
            $this->_height = $height + $padding * 2;
          }

          return $result;
      }
    }

    public function watermark($watermark, $align_x='RIGHT', $align_y='BOTTOM', $margin=5) {

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            throw new Exception('Not a valid image object');
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

          return $this->_image->compositeImage($_watermark, imagick::COMPOSITE_COPY, $offset_x, $offset_y);

        case 'gd':

          if (!$this->_image) $this->load();

          if (!$this->_image) {
            throw new Exception('Not a valid image resource');
          }

          $_watermark = new ent_image($watermark, $this->_library);

        // Return false on no image
          if (!$_watermark->type()) {
            throw new Exception('Watermark file is not a valid image ('. $watermark .')');
          }

        // Load watermark
          $_watermark->load();

        // Check if watermark is a PNG file
          if ($_watermark->type() != 'png') {
            trigger_error('Watermark file is not a PNG image ('. $watermark .')', E_USER_NOTICE);
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

    public function write($destination, $quality=90, $interlaced=false) {

      if (is_file($destination)) {
        throw new Exception('Destination already exists ('. $destination .')');
      }

      if (is_dir($destination)) {
        throw new Exception('Destination is a folder: ('. $destination .')');
      }

      if (!is_writable(pathinfo($destination, PATHINFO_DIRNAME))) {
        throw new Exception('Destination is not writable ('. $destination .')');
      }

      $type = strtolower(pathinfo($destination, PATHINFO_EXTENSION));

      if (!preg_match('#^(gif|jpe?g|png|webp)$#i', $type)) {
        throw new Exception("Unknown image output format ($type)");
      }

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            throw new Exception('Not a valid image object');
          }

          if ($this->_image->getImageDepth() > 16) {
            $this->_image->setImageDepth(16);
          }

          switch(strtolower($type)) {
            case 'jpeg':
            case 'jpg':
               $this->_image->setImageCompression(Imagick::COMPRESSION_JPEG);
               break;
            default:
               $this->_image->setImageCompression(Imagick::COMPRESSION_ZIP);
               break;
          }

          $this->_image->setImageCompressionQuality((int)$quality);

          if ($interlaced) $this->_image->setInterlaceScheme(Imagick::INTERLACE_PLANE);

          return $this->_image->writeImage($type.':'.$destination);

        case 'gd':

          if (!$this->_image) $this->load();

          if (!$this->_image) {
            throw new Exception('Not a valid image resource');
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

            case 'webp':
              if (!function_exists('ImageWebP')) {
                return $this->write(preg_replace('#\.webp$#', '.jpg', $destination), $quality, $interlaced);
              }
              ImageSaveAlpha($this->_image, true);
              $result = ImageWebP($this->_image, $destination, $quality);
              ImageDestroy($this->_image);
              return $result;

            default:
              ImageDestroy($this->_image);
              throw new Exception("Unknown image output format ($type)");
          }
      }
    }

    public function output($type='jpg', $quality=90, $interlaced=false) {

      switch($this->_library) {
        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            throw new Exception('Not a valid image object');
          }

          $this->_image->setImageFormat($type);
          $this->_image->setImageCompressionQuality((int)$quality);
          return $this->_image->getImageBlob();

        case 'gd':

          if (!$this->_image) $this->load();

          if (!$this->_image) {
            throw new Exception('Not a valid image resource');
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

            case 'webp':
              if (!function_exists('ImageWebP')) {
                return $this->output($type, $quality, $interlaced);
              }
              ImageSaveAlpha($this->_image, true);
              $result = ImageWebP($this->_image, false, $quality);
              ImageDestroy($this->_image);
              return $result;

            default:
              ImageDestroy($this->_image);
              throw new Exception('Unknown output format');
          }
      }
    }

    public function width() {

      if (!empty($this->_width)) return $this->_width;

      switch($this->_library) {
        case 'imagick':

          if (empty($this->_image)) $this->load();

          if (empty($this->_image)) {
            throw new Exception('Not a valid image object');
          }

          $this->_width = $this->_image->getImageWidth();
          break;

        case 'gd':

          if (!$this->_image) {
            list($this->_width, $this->_height) = getimagesize($this->_src);
            break;
          }

          $this->_width = ImageSX($this->_image);
          break;
      }

      if ($this->_width == 0) throw new Exception('Failed to detect image width');

      return $this->_width;
    }

    public function height() {

      if (!empty($this->_height)) return $this->_height;

      switch($this->_library) {
        case 'imagick':

          if (empty($this->_src)) $this->load();

          if (empty($this->_src)) {
            throw new Exception('Not a valid image object');
          }

          $this->_height = $this->_image->getImageHeight();
          break;

        case 'gd':

          if (!$this->_image) {
            list($this->_width, $this->_height) = getimagesize($this->_src);
            break;
          }

          $this->_height = ImageSY($this->_image);
          break;
      }

      if ($this->_height == 0) throw new Exception('Failed to detect image height');

      return $this->_height;
    }

    public function type() {

      if (!empty($this->_type)) return $this->_type;

      if (empty($this->_image)) {

        if (function_exists('exif_imagetype')) {
          $image_type = exif_imagetype($this->_src);
        }

        if (empty($image_type) && function_exists('getimagesize')) {
          $params = getimagesize($this->_src);
          $this->_width = $params[0];
          $this->_height = $params[1];
          $image_type = $params[2];
        }

        if (!empty($image_type)) {
          switch($image_type) {
            case 1:
              $this->_type = 'gif';
              break;
            case 2:
              $this->_type = 'jpg';
              break;
            case 18:
              $this->_type = 'webp';
              break;
            case 3:
            default:
              $this->_type = 'png';
              break;
          }

          return $this->_type;
        }
      }

      if (empty($this->_image)) $this->load();

      switch($this->_library) {

        case 'imagick':

          if (empty($this->_image)) {
            throw new Exception('Not a valid image object');
          }

          return $this->_image->getImageFormat();

        case 'gd':

          if (!$this->_image) {
            throw new Exception('Not a valid image resource');
          }

          return $this->_type;
      }
    }
  }
