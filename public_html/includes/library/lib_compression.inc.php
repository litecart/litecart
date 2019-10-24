<?php

  class compression {

    public static function init() {
      event::register('before_output', array(__CLASS__, 'before_output'));
    }

    public static function before_output() {

    // Initialize GZIP compression to reduce bandwidth.
      if (!headers_sent() && settings::get('gzip_enabled')) {
        if (in_array(strtolower(ini_get('zlib.output_compression')), array('', '0', 'off', 'no', 'false', 'disabled'))) {
          ob_start('ob_gzhandler');
        }
      }
    }
  }
