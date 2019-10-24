<?php

  class breadcrumbs {

    public static $data = array();

    public static function init() {

      self::add(language::translate('title_home', 'Home'), WS_DIR_APP);

      event::register('prepare_output', array(__CLASS__, 'prepare_output'));
    }

    public static function prepare_output() {

      if (count(self::$data) > 1) {
        $breadcrumbs = new ent_view();

        $breadcrumbs->snippets['breadcrumbs'] = array();
        foreach (self::$data as $breadcrumb) {
          $breadcrumbs->snippets['breadcrumbs'][] = array(
            'title' => $breadcrumb['title'],
            'link' => $breadcrumb['link'],
          );
        }

        document::$snippets['breadcrumbs'] = $breadcrumbs->stitch('views/breadcrumbs');
      }
    }

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
