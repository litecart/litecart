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
          'debugs' => array(),
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

    //public static function after_capture() {
    //}

    public static function prepare_output() {

      $notices = array();

      foreach(array('debugs', 'errors', 'notices', 'warnings', 'success') as $notice_type) {
        if (!empty(notices::$data[$notice_type])) {

          switch ($notice_type) {
            case 'warnings':
            case 'errors':
              $icon = functions::draw_fonticon('fa-exclamation-triangle');
              break;
            case 'notices':
              $icon = functions::draw_fonticon('fa-exclamation-circle');
              break;
            case 'success':
              $icon = functions::draw_fonticon('fa-check-circle');
              break;
            default:
              $icon = '';
              break;
          }

          $notices[] = '  <div class="notice '. $notice_type .'">' . $icon .' '. implode('</div>' . PHP_EOL . '  <div class="notice '. $notice_type .'">' . $icon . ' ', array_unique(notices::$data[$notice_type])) . '</div>' . PHP_EOL;
        }
      }

      self::reset();

      if (!empty($notices)) {
        document::$snippets['notices'] = '<div id="notices-wrapper">' . PHP_EOL
                                       . '  <div id="notices">'. PHP_EOL . implode(PHP_EOL, $notices) . '</div>' . PHP_EOL
                                       . '</div>' . PHP_EOL
                                       . '<script>setTimeout(function(){$("#notices-wrapper").slideUp();}, 15000);</script>';
        unset($notices);
      }
    }

    public static function before_output() {
    }

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

?>