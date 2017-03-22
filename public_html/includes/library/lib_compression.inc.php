<?php

  class compression {


    public static function construct() {
    }

    //public static function load_dependencies() {
    //}

    //public static function initiate() {
    //}

    //public static function startup() {
    //}

    //public static function before_capture() {
    //}

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    public static function before_output() {

    // Initialize GZIP compression to reduce bandwidth.
      if (!headers_sent() && settings::get('gzip_enabled')) {
        ob_start("ob_gzhandler");
      }
    }

    //public static function shutdown() {
    //}

    ######################################################################
  }
