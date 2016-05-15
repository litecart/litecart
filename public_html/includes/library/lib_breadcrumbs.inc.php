<?php

  class breadcrumbs {

    public static $data = array();

    //public static function construct() {
    //}

    //public static function load_dependencies() {
    //}

    //public static function initiate() {
    //}

    //public static function startup() {
    //}

    public static function before_capture() {
      self::add(language::translate('title_home', 'Home'), WS_DIR_HTTP_HOME);
    }

    //public static function after_capture() {
    //}

    public static function prepare_output() {

      $breadcrumbs = new view();

      $breadcrumbs->snippets['breadcrumbs'] = array();
      foreach (self::$data as $breadcrumb) {
        $breadcrumbs->snippets['breadcrumbs'][] = array(
          'title' => $breadcrumb['title'],
          'link' => $breadcrumb['link'],
        );
      }

      document::$snippets['breadcrumbs'] = $breadcrumbs->stitch('views/breadcrumbs');
    }

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function reset() {
      self::$data = array();
    }

    public static function add($title, $link=null) {
      self::$data[] = array(
        'title' => $title,
        'link' => $link,
      );
    }
  }

?>