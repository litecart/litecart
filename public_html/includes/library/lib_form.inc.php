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

        $flag_unicoded = false;
        if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'charset=utf-8') !== false) $flag_unicoded = true;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'charset') === false) $flag_unicoded = true;

        if ($flag_unicoded) {
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

?>