<?php

  class compression {

    public static function init() {
      event::register('before_output', [__CLASS__, 'before_output']);
    }

    public static function before_output() {

    // Initialize GZIP compression to reduce bandwidth.
      if (!headers_sent() && settings::get('gzip_enabled')) {
        if (filter_var(ini_get('zlib.output_compression'), FILTER_VALIDATE_BOOLEAN)) {
          ob_start('ob_gzhandler');
        }
      }
    }
  }
