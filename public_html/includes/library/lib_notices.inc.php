<?php

  class notices {
    public static $data = array();


    public static function construct() {
    }

    public static function load_dependencies() {
      if (empty(session::$data['notices'])) {
        session::$data['notices'] = array(
          'errors' => array(),
          'warnings' => array(),
          'notices' => array(),
          'success' => array(),
        );
      }

      self::$data = &session::$data['notices'];
    }

    //public static function initiate() {
    //}

    //public static function startup() {
    //}

    //public static function before_capture() {
    //}

    public static function after_capture() {

      notices::$data = array_filter(notices::$data);

      if (!empty(notices::$data)) {
        $notices = new view();
        $notices->snippets['notices'] = notices::$data;
        document::$snippets['notices'] = $notices->stitch('views/notices');
        self::reset();
      }
    }

    //public static function prepare_output() {
    //}

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function reset($type=null) {

      if ($type) {
        self::$data[$type] = array();

      } else {
        if (!empty(self::$data)) {
          foreach (self::$data as $type => $container) {
            self::$data[$type] = array();
          }
        }
      }
    }

    public static function add($type, $msg, $key=false) {
      if ($key) self::$data[$type][$key] = $msg;
      else self::$data[$type][] = $msg;
    }

    public static function remove($type, $key) {
      unset(self::$data[$type][$key]);
    }

    public static function get($type) {
      if (!isset(self::$data[$type])) return false;
      return self::$data[$type];
    }

    public static function dump($type) {
      $stack = self::$data[$type];
      self::$data[$type] = array();
      return $stack;
    }
  }
