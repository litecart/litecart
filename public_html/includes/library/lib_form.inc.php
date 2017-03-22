<?php

  class form {

    //public static function construct() {
    //}

    //public static function load_dependencies() {
    //}

    //public static function initiate() {
    //}

    public static function startup() {

    // Is there incoming ajax data that needs decoding?
      if (!empty($_POST) && strtolower(language::$selected['charset']) != 'utf-8') {

        $unicoded_content = false;
        if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'charset=utf-8') !== false) $unicoded_content = true;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'charset') === false) $flag_unicoded = true;

        if ($unicoded_content) {
          function utf8_decode_recursive($input) {
            $return = array();
            foreach ($input as $key => $val) {
              if (is_array($val)) $return[$key] = utf8_decode_recursive($val);
              else $return[$key] = utf8_decode($val);
            }
            return $return;
          }
          $_POST = utf8_decode_recursive($_POST);
        }
      }
    }

    //public static function before_capture() {
    //}

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function session_post_token() {
      return sha1(PLATFORM_NAME . PLATFORM_VERSION . session::get_id());
    }
  }
