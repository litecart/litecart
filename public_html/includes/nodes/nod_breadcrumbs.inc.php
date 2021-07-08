<?php

  class breadcrumbs {

    public static $data = [];

    public static function init() {

      self::add(language::translate('title_home', 'Home'), WS_DIR_APP);

      event::register('after_capture', [__CLASS__, 'after_capture']);
    }

    public static function after_capture() {

      if (count(self::$data) > 1) {
        $breadcrumbs = new ent_view();

        $breadcrumbs->snippets['breadcrumbs'] = [];
        foreach (self::$data as $breadcrumb) {
          $breadcrumbs->snippets['breadcrumbs'][] = [
            'title' => $breadcrumb['title'],
            'link' => $breadcrumb['link'],
          ];
        }

        document::$snippets['breadcrumbs'] = $breadcrumbs->stitch('views/breadcrumbs.inc.php');
      }
    }

    ######################################################################

    public static function reset() {
      self::$data = [];
    }

    public static function add($title, $link=null) {
      self::$data[] = [
        'title' => $title,
        'link' => $link,
      ];
    }
  }
